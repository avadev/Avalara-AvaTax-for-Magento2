#!/usr/bin/env bash

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

mkdir -p "$HOME/.php-cs-fixer"

# go into the parent folder and pull a full magento 2 ce project, to do all tests.
echo "==> Installing Magento 2 CE (Version $magento) over composer create-project ..."
cd ..
composer create-project "magento/community-edition:$magento" magento-ce
cd "magento-ce"

# require the classyllama extension to make it usable (autoloading)
echo "==> Requiring classyllama/module-avatax from the dev-$TRAVIS_BRANCH branch"
composer config repositories.erikhansen/module-avatax git https://github.com/erikhansen/ClassyLlama_AvaTax.git
composer require "classyllama/module-avatax:dev-$TRAVIS_BRANCH"

echo "==> Installing Magento 2"
mysql -uroot -e 'CREATE DATABASE magento2;'
php bin/magento setup:install -q --admin-user="admin" --admin-password="123123q" --admin-email="admin@example.com" --admin-firstname="John" --admin-lastname="Doe" --db-name="magento2"

echo "==> Copying the current build to the Magento 2 installation."
cp -R ../magento2/* vendor/classyllama/module-avatax/

# enable the extension, do other relevant mage tasks.
echo "==> Enable extension, do mage tasks..."
php bin/magento module:enable ClassyLlama_AvaTax
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile

# definition for the test suites
test_suites=("integration" "unit")
integration_levels=(1 2 3)

for test_suite in ${test_suites[@]}; do
    # prepare for test suite
    case ${test_suite} in
        integration)
            cd dev/tests/integration

            test_set_list=$(find testsuite/* -maxdepth 1 -mindepth 1 -type d | sort)
            test_set_count=$(printf "$test_set_list" | wc -l)
            test_set_size[1]=$(printf "%.0f" $(echo "$test_set_count*0.12" | bc))  #12%
            test_set_size[2]=$(printf "%.0f" $(echo "$test_set_count*0.32" | bc))  #32%
            test_set_size[3]=$((test_set_count-test_set_size[1]-test_set_size[2])) #56%
            echo "Total = ${test_set_count}; Batch #1 = ${test_set_size[1]}; Batch #2 = ${test_set_size[2]}; Batch #3 = ${test_set_size[3]};";

            for integration_index in ${integration_levels[@]}; do
                echo "==> preparing integration testsuite on index $integration_index with set size of ${test_set_size[$integration_index]}"
                cp phpunit.xml.dist phpunit.xml

                # divide test sets up by indexed testsuites
                i=0; j=1; dirIndex=1; testIndex=1;
                for test_set in $test_set_list; do
                    test_xml[j]+="            <directory suffix=\"Test.php\">$test_set</directory>\n"

                    if [[ $j -eq $integration_index ]]; then
                        echo "$dirIndex: Batch #$j($testIndex of ${test_set_size[$j]}): + including $test_set"
                    else
                        echo "$dirIndex: Batch #$j($testIndex of ${test_set_size[$j]}): + excluding $test_set"
                    fi

                    testIndex=$((testIndex+1))
                    dirIndex=$((dirIndex+1))
                    i=$((i+1))
                    if [ $i -eq ${test_set_size[$j]} ] && [ $j -lt $INTEGRATION_SETS ]; then
                        j=$((j+1))
                        i=0
                        testIndex=1
                    fi
                done

                # replace test sets for current index into testsuite
                perl -pi -e "s#\s+<directory.*>testsuite</directory>#${test_xml[integration_index]}#g" phpunit.xml
            done

            echo "==> testsuite preparation complete"

            # create database and move db config into place
            mysql -uroot -e '
                SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION;
                CREATE DATABASE magento_integration_tests;
            '
            mv etc/install-config-mysql.travis.php.dist etc/install-config-mysql.php

            cd ../../..
            ;;
        static)
            cd dev/tests/static

            echo "==> preparing changed files list"
            changed_files_ce="$TRAVIS_BUILD_DIR/dev/tests/static/testsuite/Magento/Test/_files/changed_files_ce.txt"
            php get_github_changes.php \
                --output-file="$changed_files_ce" \
                --base-path="$TRAVIS_BUILD_DIR" \
                --repo='https://github.com/magento/magento2.git' \
                --branch='develop'
            cat "$changed_files_ce" | sed 's/^/  + including /'

            cd ../../..

            cp package.json.sample package.json
            cp Gruntfile.js.sample Gruntfile.js
            yarn
            ;;
        js)
            cp package.json.sample package.json
            cp Gruntfile.js.sample Gruntfile.js
            yarn

            echo "Deploying Static Content"
            php bin/magento setup:static-content:deploy -f -q -j=2 --no-css --no-less --no-images --no-fonts --no-misc --no-html-minify
            ;;
    esac
done

# go into the actual cloned repo to do make preparations for the EQP tests.
echo "==> Doing preparations for EQP tests."
cd ../magento2
composer update
./vendor/bin/phpcs --config-set installed_paths vendor/magento/marketplace-eqp

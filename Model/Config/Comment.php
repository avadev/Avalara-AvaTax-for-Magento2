<?php

namespace ClassyLlama\AvaTax\Model\Config;

use Magento\Config\Model\Config\CommentInterface;

use ClassyLlama\AvaTax\Framework\AppInterface;

class Comment implements CommentInterface
{
    /**
     * Set version of extension to admin configuration
     *
     * @param $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        return sprintf('<p><strong>%s</strong></p>', AppInterface::APP_VERSION );
    }
}

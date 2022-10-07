drop table
    {prefix}_avatax_cross_border_class_country,
    {prefix}_avatax_log,
    {prefix}_avatax_queue,
    {prefix}_avatax_quote_item,
    {prefix}_avatax_sales_creditmemo,
    {prefix}_avatax_sales_creditmemo_item,
    {prefix}_avatax_sales_invoice_item,
    {prefix}_avatax_sales_order,
    {prefix}_avatax_sales_order_item,
    {prefix}_classyllama_avatax_crossbordertype,
    {prefix}_avatax_cross_border_class,
    {prefix}_avatax_batch_queue,
    {prefix}_avatax_sales_invoice;

delete from {prefix}_setup_module where module = 'ClassyLlama_AvaTax';
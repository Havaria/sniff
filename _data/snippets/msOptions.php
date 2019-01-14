id: 15
source: 1
name: msOptions
category: miniShop2
properties: 'a:3:{s:7:"product";a:7:{s:4:"name";s:7:"product";s:4:"desc";s:16:"ms2_prop_product";s:4:"type";s:11:"numberfield";s:7:"options";a:0:{}s:5:"value";s:0:"";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:7:"options";a:7:{s:4:"name";s:7:"options";s:4:"desc";s:16:"ms2_prop_options";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:5:"value";s:0:"";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:3:"tpl";a:7:{s:4:"name";s:3:"tpl";s:4:"desc";s:12:"ms2_prop_tpl";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:5:"value";s:13:"tpl.msOptions";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}}'
static_file: core/components/minishop2/elements/snippets/snippet.ms_options.php

-----

/** @var modX $modx */
/** @var array $scriptProperties */
$tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.msOptions');
if (!empty($input) && empty($product)) {
    $product = $input;
}
if (!empty($name) && empty($options)) {
    $options = $name;
}

$product = !empty($product) && $product != $modx->resource->id
    ? $modx->getObject('msProduct', array('id' => $product))
    : $modx->resource;
if (!($product instanceof msProduct)) {
    return "[msOptions] The resource with id = {$product->id} is not instance of msProduct.";
}

$names = array_map('trim', explode(',', $options));
$options = array();
foreach ($names as $name) {
    if (!empty($name) && $option = $product->get($name)) {
        if (!is_array($option)) {
            $option = array($option);
        }
        if (!empty($option[0])) {
            $options[$name] = $option;
        }
    }
}

/** @var pdoTools $pdoTools */
$pdoTools = $modx->getService('pdoTools');

return $pdoTools->getChunk($tpl, array(
    'id' => $product->id,
    'options' => $options,
));
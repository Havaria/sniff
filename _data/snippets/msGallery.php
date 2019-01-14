id: 14
source: 1
name: msGallery
category: miniShop2
properties: 'a:10:{s:7:"product";a:7:{s:4:"name";s:7:"product";s:4:"desc";s:16:"ms2_prop_product";s:4:"type";s:11:"numberfield";s:7:"options";a:0:{}s:5:"value";s:0:"";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:3:"tpl";a:7:{s:4:"name";s:3:"tpl";s:4:"desc";s:12:"ms2_prop_tpl";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:5:"value";s:13:"tpl.msGallery";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:5:"limit";a:7:{s:4:"name";s:5:"limit";s:4:"desc";s:14:"ms2_prop_limit";s:4:"type";s:11:"numberfield";s:7:"options";a:0:{}s:5:"value";i:0;s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:6:"offset";a:7:{s:4:"name";s:6:"offset";s:4:"desc";s:15:"ms2_prop_offset";s:4:"type";s:11:"numberfield";s:7:"options";a:0:{}s:5:"value";i:0;s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:6:"sortby";a:7:{s:4:"name";s:6:"sortby";s:4:"desc";s:15:"ms2_prop_sortby";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:5:"value";s:4:"rank";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:7:"sortdir";a:7:{s:4:"name";s:7:"sortdir";s:4:"desc";s:16:"ms2_prop_sortdir";s:4:"type";s:4:"list";s:7:"options";a:2:{i:0;a:2:{s:4:"text";s:3:"ASC";s:5:"value";s:3:"ASC";}i:1;a:2:{s:4:"text";s:4:"DESC";s:5:"value";s:4:"DESC";}}s:5:"value";s:3:"ASC";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:13:"toPlaceholder";a:7:{s:4:"name";s:13:"toPlaceholder";s:4:"desc";s:22:"ms2_prop_toPlaceholder";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:5:"value";s:0:"";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:7:"showLog";a:7:{s:4:"name";s:7:"showLog";s:4:"desc";s:16:"ms2_prop_showLog";s:4:"type";s:13:"combo-boolean";s:7:"options";a:0:{}s:5:"value";b:0;s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:5:"where";a:7:{s:4:"name";s:5:"where";s:4:"desc";s:14:"ms2_prop_where";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:5:"value";s:0:"";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}s:8:"filetype";a:7:{s:4:"name";s:8:"filetype";s:4:"desc";s:17:"ms2_prop_filetype";s:4:"type";s:9:"textfield";s:7:"options";a:0:{}s:5:"value";s:0:"";s:7:"lexicon";s:20:"minishop2:properties";s:4:"area";s:0:"";}}'
static_file: core/components/minishop2/elements/snippets/snippet.ms_gallery.php

-----

/** @var modX $modx */
/** @var array $scriptProperties */
/** @var miniShop2 $miniShop2 */
$miniShop2 = $modx->getService('miniShop2');
$miniShop2->initialize($modx->context->key);
/** @var pdoFetch $pdoFetch */
if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
    return false;
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded.');

$extensionsDir = $modx->getOption('extensionsDir', $scriptProperties, 'components/minishop2/img/mgr/extensions/', true);
$limit = $modx->getOption('limit', $scriptProperties, 0);
$tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.msGallery');

/** @var msProduct $product */
$product = !empty($product) && $product != $modx->resource->id
    ? $modx->getObject('msProduct', array('id' => $product))
    : $modx->resource;
if (!$product || !($product instanceof msProduct)) {
    return "[msGallery] The resource with id = {$product->id} is not instance of msProduct.";
}

$where = array(
    'product_id' => $product->id,
    'parent' => 0,
);
if (!empty($filetype)) {
    $where['type:IN'] = array_map('trim', explode(',', $filetype));
}
if (empty($showInactive)) {
    $where['active'] = 1;
}
$select = array(
    'msProductFile' => '*',
);

// Add user parameters
foreach (array('where') as $v) {
    if (!empty($scriptProperties[$v])) {
        $tmp = $scriptProperties[$v];
        if (!is_array($tmp)) {
            $tmp = json_decode($tmp, true);
        }
        if (is_array($tmp)) {
            $$v = array_merge($$v, $tmp);
        }
    }
    unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

$default = array(
    'class' => 'msProductFile',
    'where' => $where,
    'select' => $select,
    'limit' => $limit,
    'sortby' => 'rank',
    'sortdir' => 'ASC',
    'fastMode' => false,
    'return' => 'data',
    'nestedChunkPrefix' => 'minishop2_',
);
// Merge all properties and run!
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$rows = $pdoFetch->run();

$pdoFetch->addTime('Fetching thumbnails');

$resolution = array();
/** @var msProductData $data */
if ($data = $product->getOne('Data')) {
    if ($data->initializeMediaSource()) {
        $properties = $data->mediaSource->getProperties();
        if (isset($properties['thumbnails']['value'])) {
            $fileTypes = json_decode($properties['thumbnails']['value'], true);
            foreach ($fileTypes as $k => $v) {
                if (!is_numeric($k)) {
                    $resolution[] = $k;
                } elseif (!empty($v['name'])) {
                    $resolution[] = $v['name'];
                } else {
                    $resolution[] = @$v['w'] . 'x' . @$v['h'];
                }
            }
        }
    }
}

// Processing rows
$files = array();
foreach ($rows as $row) {
    if (isset($row['type']) && $row['type'] == 'image') {
        $c = $modx->newQuery('msProductFile', array('parent' => $row['id']));
        $c->select('product_id,url');
        $tstart = microtime(true);
        if ($c->prepare() && $c->stmt->execute()) {
            $modx->queryTime += microtime(true) - $tstart;
            $modx->executedQueries++;
            while ($tmp = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                if (preg_match("#/{$tmp['product_id']}/(.*?)/#", $tmp['url'], $size)) {
                    $row[$size[1]] = $tmp['url'];
                }
            }
        }
    } elseif (isset($row['type'])) {
        $row['thumbnail'] = file_exists(MODX_ASSETS_PATH . $extensionsDir . $row['type'] . '.png')
            ? MODX_ASSETS_URL . $extensionsDir . $row['type'] . '.png'
            : MODX_ASSETS_URL . $extensionsDir . 'other.png';
        foreach ($resolution as $v) {
            $row[$v] = $row['thumbnail'];
        }
    }

    $files[] = $row;
}

$output = $pdoFetch->getChunk($tpl, array(
    'files' => $files,
));

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $output .= '<pre class="msGalleryLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
} else {
    return $output;
}
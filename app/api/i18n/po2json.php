<?php

use Gettext\Generator\JsonGenerator;
use Gettext\Loader\PoLoader;

global $current_lang;

$current_lang_path = APPPATH . '/i18n/' . $current_lang . '/LC_MESSAGES';

//Load the po file with the translations
$loader = new PoLoader();
$translations = $loader->loadFile($current_lang_path . '/messages.po');
$generator = new JsonGenerator();

//Export to a json file
try {
    $res = $generator->generateFile($translations, $current_lang_path . "/messages.json");
    if ($res) {
        to_json(['error' => FALSE, 'message' => __('JSON file generated successfully: ' . site_url('app/i18n/' . $current_lang . '/LC_MESSAGES/messages.json'))]);
    } else {
        to_json(['error' => FALSE, 'message' => __('The JSON could not be generated')]);
    }
} catch (Exception $ex) {
    to_json(['error' => TRUE, 'message' => $ex->getMessage()]);
}

<?php
global $lrvdb, $tabla;
/** @var \Tipsy\Request $Request */
$config = (object)$Request->config;
$filters = $Request->filters;
$tabla = 'users';

$current_page = intval($config->current_page);
$per_page = intval($config->per_page);
$start = ($current_page - 1) * $per_page;
$orderby = [
    "id {$config->order}"
];
$filtros = [];
$filtros['role'] = ['administrador'];
foreach ($filters as $filter){
    $filtros[$filter['key']] = $filter['value'];
}
$str_filtros = count($filtros) ?  where_filtros($filtros) : "";
$args_query = $str_filtros;
$args_query .= (count($orderby)) ? " ORDER BY ".implode(",",$orderby) : "";
$args_query_tot = $args_query;
$args_query .= (($per_page>0) ? " LIMIT {$start},{$per_page}" : "");
$query = "SELECT id as id_user, name, lastname, username, email, registered, last_login FROM {$tabla} {$args_query}";
$rows = $lrvdb->get_results($query);
$total = (strlen($str_filtros)) ? $lrvdb->get_var("SELECT COUNT(*) FROM {$tabla} {$args_query_tot}") : $lrvdb->get_var("SELECT COUNT(*) FROM {$tabla}");
$rows = !empty($rows) ? $rows : [];
foreach($rows as $row){
    $row->registered = unix2local($row->registered);
    $row->last_login = !empty($row->last_login) ? unix2local($row->last_login) : 'Nunca';
}

to_json(["error" => FALSE, "rows" => $rows, "total" => intval($total), 'query' => $query]);

//Convierte un array en una sentencia where para los filtros
function where_filtros($filtros){
    global $tabla;
    $where = array();
    foreach ($filtros as $k => $v) {
        if(empty($v)) continue;
        switch ($k) {
            default:
                $where[] = "{$k} ".(is_array($v) ? "IN ('".implode("','", $v)."')" :  "LIKE '%{$v}%'");
                break;
        }
    }
    return count($where) ? "WHERE ".implode(' AND ',$where) : "";
}
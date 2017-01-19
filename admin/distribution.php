<?php

/**
 * ECSHOP 配送站点管理程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user_rank.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$exc = new exchange($ecs->table("distribution"), $db, 'distribution_id', 'distribution_name');

/*------------------------------------------------------ */
//-- 配送站点列表
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list')
{
    $distribution_list = array();
    $distribution_list = $db->getAll("SELECT * FROM " .$ecs->table('distribution'));

    $smarty->assign('ur_here',      $_LANG['09_distribution_list']);
    $smarty->assign('action_link',  array('text' => '添加配送站点', 'href'=>'distribution.php?act=add'));
    $smarty->assign('full_page',    1);

    $smarty->assign('distribution_list',   $distribution_list);

    assign_query_info();
    $smarty->display('distribution.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $distribution_list = array();
    $distribution_list = $db->getAll("SELECT * FROM " .$ecs->table('distribution'));

    $smarty->assign('distribution_list',   $distribution_list);
    make_json_result($smarty->fetch('distribution.htm'));
}

/*------------------------------------------------------ */
//-- 添加配送站点
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'add')
{
    admin_priv('ship_manage');

    $distribution['distribution_id']      = 0;
    $distribution['distribution_name'] = '';

    $form_action          = 'insert';

    $smarty->assign('distribution',        $distribution);
    $smarty->assign('ur_here',     '添加配送站点');
    $smarty->assign('action_link', array('text' => $_LANG['09_distribution_list'], 'href'=>'distribution.php?act=list'));
    $smarty->assign('form_action', $form_action);

    assign_query_info();
    $smarty->display('distribution_info.htm');
}

/*------------------------------------------------------ */
//-- 增加配送站点到数据库
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'insert')
{
    admin_priv('ship_manage');
    
    if(empty($_POST['distribution_name'])){
      sys_msg('站点名称不能为空', 1);
    }

    /* 检查是否存在重名的配送站点 */
    if (!$exc->is_only('distribution_name', trim($_POST['distribution_name'])))
    {
        sys_msg(sprintf('该站点已经存在', trim($_POST['distribution_name'])), 1);
    }

    $sql = "INSERT INTO " .$ecs->table('distribution') ."( ".
                "distribution_name) VALUES (".
                "'$_POST[distribution_name]')";
    $db->query($sql);

    /* 管理员日志 */
    admin_log(trim($_POST['distribution_name']), 'add', 'distribution');
    clear_cache_files();

    $lnk[] = array('text' => $_LANG['back_list'],    'href'=>'distribution.php?act=list');
    $lnk[] = array('text' => $_LANG['add_continue'], 'href'=>'distribution.php?act=add');
    sys_msg('添加配送站点成功', 0, $lnk);
}

/*------------------------------------------------------ */
//-- 删除配送站点
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('ship_manage');

    $distribution_id = intval($_GET['id']);

    if ($exc->drop($distribution_id))
    {
        $distribution_name = $exc->get_name($distribution_id);
        admin_log(addslashes($rank_name), 'remove', 'distribution');
        clear_cache_files();
    }

    $url = 'distribution.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;

}
?>
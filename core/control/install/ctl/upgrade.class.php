<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if (!defined("IN_BAIGO")) {
    exit("Access Denied");
}

include_once(BG_PATH_CLASS . "tpl.class.php"); //载入模板类

class CONTROL_UPGRADE {

    private $obj_tpl;

    function __construct() { //构造函数
        $this->obj_base = $GLOBALS["obj_base"];
        $this->config   = $this->obj_base->config;
        $this->obj_tpl  = new CLASS_TPL(BG_PATH_TPL . "install/" . BG_DEFAULT_UI);
        $this->obj_dir  = new CLASS_DIR(); //初始化目录对象
        $this->obj_dir->mk_dir(BG_PATH_CACHE . "ssin");
        $this->upgrade_init();
    }


    function ctl_ext() {
        $this->obj_tpl->tplDisplay("upgrade_ext.tpl", $this->tplData);

        return array(
            "alert" => "y030403",
        );
    }


    function ctl_dbconfig() {
        if ($this->errCount > 0) {
            return array(
                "alert" => "x030414",
            );
        }

        $this->obj_tpl->tplDisplay("upgrade_dbconfig.tpl", $this->tplData);

        return array(
            "alert" => "y030404",
        );
    }


    function ctl_form() {
        if ($this->errCount > 0) {
            return array(
                "alert" => "x030414",
            );
        }

        if (!$this->check_db()) {
            return array(
                "alert" => "x030412",
            );
        }

        $this->obj_tpl->tplDisplay("upgrade_form.tpl", $this->tplData);

        return array(
            "alert" => "y030405",
        );
    }


    /**
     * upgrade_2 function.
     *
     * @access public
     * @return void
     */
    function ctl_dbtable() {
        if ($this->errCount > 0) {
            return array(
                "alert" => "x030414",
            );
        }

        if (!$this->check_db()) {
            return array(
                "alert" => "x030412",
            );
        }

        $this->table_admin();
        /*$this->table_advert();
        $this->table_media();
        $this->table_posi();
        $this->table_stat();*/
        $this->table_session();

        $this->obj_tpl->tplDisplay("upgrade_dbtable.tpl", $this->tplData);

        return array(
            "alert" => "y030404",
        );
    }


    function ctl_over() {
        if ($this->errCount > 0) {
            return array(
                "alert" => "x030414",
            );
        }

        if (!$this->check_db()) {
            return array(
                "alert" => "x030412",
            );
        }

        $this->obj_tpl->tplDisplay("upgrade_over.tpl", $this->tplData);

        return array(
            "alert" => "y030405",
        );
    }


    private function check_db() {
        if (strlen(BG_DB_HOST) < 1 || strlen(BG_DB_NAME) < 1 || strlen(BG_DB_USER) < 1 || strlen(BG_DB_PASS) < 1 || strlen(BG_DB_CHARSET) < 1) {
            return false;
        } else {
            if (!defined("BG_DB_PORT")) {
                define("BG_DB_PORT", "3306");
            }

            $_cfg_host = array(
                "host"      => BG_DB_HOST,
                "name"      => BG_DB_NAME,
                "user"      => BG_DB_USER,
                "pass"      => BG_DB_PASS,
                "charset"   => BG_DB_CHARSET,
                "debug"     => BG_DEBUG_DB,
                "port"      => BG_DB_PORT,
            );

            $GLOBALS["obj_db"]   = new CLASS_MYSQLI($_cfg_host); //设置数据库对象
            $this->obj_db        = $GLOBALS["obj_db"];

            if (!$this->obj_db->connect()) {
                return false;
            }

            if (!$this->obj_db->select_db()) {
                return false;
            }

            return true;
        }
    }


    private function upgrade_init() {
        $_arr_extRow      = get_loaded_extensions();
        $this->errCount   = 0;

        foreach ($this->obj_tpl->type["ext"] as $_key=>$_value) {
            if (!in_array($_key, $_arr_extRow)) {
                $this->errCount++;
            }
        }

        $_act_get = fn_getSafe(fn_get("act_get"), "txt", "ext");

        $this->tplData = array(
            "errCount"   => $this->errCount,
            "extRow"     => $_arr_extRow,
            "act_get"    => $_act_get,
            "act_next"   => $this->upgrade_next($_act_get),
        );
    }


    private function upgrade_next($act_get) {
        $_arr_optKeys = array_keys($this->obj_tpl->opt);
        $_index       = array_search($act_get, $_arr_optKeys);
        $_arr_opt     = array_slice($this->obj_tpl->opt, $_index + 1, 1);
        if ($_arr_opt) {
            $_key = key($_arr_opt);
        } else {
            $_key = "over";
        }

        return $_key;
    }


    private function table_admin() {
        include_once(BG_PATH_MODEL . "admin.class.php"); //载入管理帐号模型
        $_mdl_admin             = new MODEL_ADMIN();
        $_mdl_admin->adminTypes = $this->obj_tpl->type["admin"];
        $_arr_adminTable        = $_mdl_admin->mdl_alert_table();

        $this->tplData["db_alert"]["admin_table"] = array(
            "alert"   => "y020111",
            "status"  => "y",
        );
    }


    private function table_advert() {
        $this->tplData["db_alert"]["advert_table"] = array(
            "alert"   => "y080111",
            "status"  => "y",
        );
    }


    private function table_media() {
        $this->tplData["db_alert"]["media_table"] = array(
            "alert"   => "y070111",
            "status"  => "y",
        );
    }


    private function table_posi() {
        include_once(BG_PATH_MODEL . "posi.class.php"); //载入管理帐号模型
        $_mdl_posi        = new MODEL_POSI();
        $_arr_posiAlert   = $_mdl_posi->mdl_alert_table();

        $this->tplData["db_alert"]["posi_alert"] = array(
            "alert"   => $_arr_posiAlert["alert"],
            "status"  => substr($_arr_posiAlert["alert"], 0, 1),
        );
    }


    private function table_stat() {
        $this->tplData["db_alert"]["stat_table"] = array(
            "alert"   => "y090111",
            "status"  => "y",
        );
    }


    private function table_session() {
        include_once(BG_PATH_MODEL . "session.class.php"); //载入管理帐号模型
        $_mdl_session       = new MODEL_SESSION();
        $_arr_sessionTable  = $_mdl_session->mdl_create_table();

        $this->tplData["db_alert"]["session_table"] = array(
            "alert"   => $_arr_sessionTable["alert"],
            "status"  => substr($_arr_sessionTable["alert"], 0, 1),
        );
    }
}

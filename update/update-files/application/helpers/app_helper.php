<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function is_admin($id = FALSE)
{
    $CI =& get_instance();
    
    if($CI->ion_auth->is_admin($id)){
        return true;
    }
    return false;
}

function is_member($id = FALSE)
{
    $CI =& get_instance();
    
    if($CI->ion_auth->is_member($id)){
        return true;
    }
    return false;
}

function is_editor($user_id='')
{
    $CI =& get_instance();
    if(empty($user_id)){
        $user_id = $CI->session->userdata('user_id');
    }
    $workspace_id = $CI->session->userdata('workspace_id');
    $query = $CI->db->query("SELECT id FROM workspace WHERE id=$workspace_id AND FIND_IN_SET($user_id,admin_id)");
    $config = $query->row_array();
    if(!empty($config)){
        return true;
    }else{
        return false;
    }
} 

function is_client($id = FALSE)
{
    $CI =& get_instance();
    
    if($CI->ion_auth->is_client($id)){
        return true;
    }
    return false;
}

function is_rtl($lang='')
{   
    $CI =& get_instance();
    if(empty($lang)){
        $lang = $CI->session->userdata('site_lang');
    }
    $CI->db->from('languages');
    $CI->db->where(['language'=>$lang, 'is_rtl'=>1]);
    $query = $CI->db->get();
    $config = $query->result_array();
    if(!empty($config)){
        return true;
    }else{
        return false;
    }
} 

function get_system_fonts()
{   
    $CI =& get_instance();
    $CI->db->from('settings');
    $CI->db->where(['type'=>'general']);
    $query = $CI->db->get();
    $config = $query->result_array();
    $config = json_decode($config[0]['data']);

    if(isset($config->system_font) && !empty($config->system_font)){
        $my_fonst = $config->system_font;
    }else{
        $my_fonst = 'default';
    }
    $return_my_fonts = '';
    $my_fonts = json_decode(file_get_contents("assets/fonts/my-fonts.json"));
    if(!empty($my_fonts) && is_array($my_fonts)){
        foreach($my_fonts as $my_font){
            if($my_font->id == $my_fonst){
                $return_my_fonts = $my_font;
            }
        }
    }else{
        return false;
    }
    if(!empty($return_my_fonts)){
        return $return_my_fonts;
    }else{
        return 'default';
    }
} 

// to get 'system_configurations' from settings table
function get_system_settings($setting_type)
{
    $CI =& get_instance();
    $CI->db->from('settings');
    $CI->db->where(['type'=>$setting_type]);
    $query = $CI->db->get();
    $config = $query->result_array();
    // $config = json_decode($config[0]['data'],1);
    if(!empty($config)){
        return $config;
    }else{
        return false;
    }
} 

function hide_budget()
{
    $CI =& get_instance();
    
    if($CI->ion_auth->is_admin()){
        return false;
    }
    
    $CI->db->from('settings');
    $CI->db->where(['type'=>'general']);
    $query = $CI->db->get();
    $config = $query->result_array();
    $config = json_decode($config[0]['data']);

    if(isset($config->hide_budget) && !empty($config->hide_budget) && $config->hide_budget == 1){
        return true;
    }else{
        return false;
    }

}


function is_leaves_editor($user_id='')
{
    $CI =& get_instance();
    if(empty($user_id)){
        $user_id = $CI->session->userdata('user_id');
    }
    $workspace_id = $CI->session->userdata('workspace_id');
    $query = $CI->db->query("SELECT id FROM workspace WHERE id=$workspace_id AND FIND_IN_SET($user_id,leave_editors)");
    $config = $query->row_array();
    if(!empty($config)){
        return true;
    }else{
        return false;
    }
}


function get_workspace($id='')
{
    $CI =& get_instance();
    if(empty($id)){
        $id = $CI->session->userdata('workspace_id');
    }
    $CI->db->from('workspace');
    $CI->db->where(['id'=>$id]);
    $query = $CI->db->get();
    $config = $query->result_array();
    if(!empty($config)){
        return $config[0]['title'];
    }
}
 
function get_system_version()
{
    $CI =& get_instance();
    $CI->db->from('updates');
    $CI->db->order_by("id","desc");
    $query = $CI->db->get();
    $config = $query->result_array();
    if(!empty($config)){
        return $config[0]['version'];
    }
}

function get_languages()
{
    $CI =& get_instance();
    $CI->db->from('languages');
    // $CI->db->where(['type'=>$setting_type]);
    $query = $CI->db->get();
    $config = $query->result_array();
    // $config = json_decode($config[0]['data'],1);
    if(!empty($config)){
        return $config;
    }
}

function getTimezoneOptions(){
    $list = DateTimeZone::listAbbreviations();
    $idents = DateTimeZone::listIdentifiers();
    
        $data = $offset = $added = array();
        foreach ($list as $abbr => $info) {
            foreach ($info as $zone) {
                if ( ! empty($zone['timezone_id'])
                    AND
                    ! in_array($zone['timezone_id'], $added)
                    AND 
                      in_array($zone['timezone_id'], $idents)) {
                    $z = new DateTimeZone($zone['timezone_id']);
                    $c = new DateTime(null, $z);
                    $zone['time'] = $c->format('H:i a');
                    $offset[] = $zone['offset'] = $z->getOffset($c);
                    $data[] = $zone;
                    $added[] = $zone['timezone_id'];
                }
            }
        }
    
        array_multisort($offset, SORT_ASC, $data);
        
        $i = 0;$temp = array();
        foreach ($data as $key => $row) {
            $temp[0] = $row['time'];
            $temp[1] = formatOffset($row['offset']);
            $temp[2] = $row['timezone_id'];
            $options[$i++] = $temp;
        }
        
        if(!empty($options)){
            return $options;
        }
}

function formatOffset($offset) {
    $hours = $offset / 3600;
    $remainder = $offset % 3600;
    $sign = $hours > 0 ? '+' : '-';
    $hour = (int) abs($hours);
    $minutes = (int) abs($remainder / 60);

    if ($hour == 0 AND $minutes == 0) {
        $sign = ' ';
    }
    return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT).':'. str_pad($minutes,2, '0');
}


function get_compnay_title()
{
    $CI =& get_instance();
    $CI->db->from('settings');
    $CI->db->where(['type'=>'general']);
    $query = $CI->db->get();
    $config = $query->result_array();
    $config = json_decode($config[0]['data']);

    if(!empty($config->company_title)){
        return $config->company_title;
    }
} 

function get_compnay_logo()
{
    $CI =& get_instance();
    $CI->db->from('settings');
    $CI->db->where(['type'=>'general']);
    $query = $CI->db->get();
    $config = $query->result_array();
    $config = json_decode($config[0]['data']);

    if(!empty($config->full_logo)){
        return $config->full_logo;
    }
} 

function get_currency_symbol()
{
    $CI =& get_instance();
    $CI->db->from('settings');
    $CI->db->where(['type'=>'general']);
    $query = $CI->db->get();
    $config = $query->result_array();
    $config = json_decode($config[0]['data']);

    if(!empty($config->currency_symbol)){
        return $config->currency_symbol;
    }elseif(!empty($config->currency_shortcode)){
        return $config->currency_shortcode;
    }else{
        return '$';
    }
} 

function get_admin_email()
{
    $CI =& get_instance();
    $CI->db->from('settings');
    $CI->db->where(['type'=>'email']);
    $query = $CI->db->get();
    $config = $query->result_array();
    $config = json_decode($config[0]['data']);
   
    if(!empty($config->email)){
        return  $config->email;
    }
}
function get_mail_type()

{

    $CI =& get_instance();

    $CI->db->from('settings');

    $CI->db->where(['type'=>'email']);

    $query = $CI->db->get();

    $config = $query->result_array();

    $config = json_decode($config[0]['data']);

   

    if(!empty($config->mail_content_type)){

        return  $config->mail_content_type;

    }

}

function get_half_logo()
{
    $CI =& get_instance();
    $CI->db->from('settings');
    $CI->db->where(['type'=>'general']);
    $query = $CI->db->get();
    $config = $query->result_array();
    $config = json_decode($config[0]['data']);
 
    if(!empty($config->half_logo)){
        return  $config->half_logo;
    }
} 

function get_full_logo()
{
    $CI =& get_instance();
    $CI->db->from('settings');
    $CI->db->where(['type'=>'general']);
    $query = $CI->db->get();
    $config = $query->result_array();
    $config = json_decode($config[0]['data']);
 
    if(!empty($config->full_logo)){
        return  $config->full_logo;
    }
} 

function get_base_url()
{
    $CI =& get_instance();
    $CI->db->from('settings');
    $CI->db->where(['type'=>'general']);
    $query = $CI->db->get();
    $config = $query->result_array();
    $config = json_decode($config[0]['data']);
 
    if(!empty($config->app_url)){
        return $config->app_url;
    }else{
        return false;
    }
}

function get_count($field,$table,$where = ''){ 
    if(!empty($where))
        $where = "where ".$where;
        
    $CI =& get_instance();
    $query = $CI->db->query("SELECT COUNT(".$field.") as total FROM ".$table." ".$where." ");
    $res = $query->result_array();
    if(!empty($res)){
        return $res[0]['total'];
    }
    
}

function get_chat_count(){ 
    $CI =& get_instance();
    $user_id = $CI->session->userdata('user_id');
    $workspace_id = $CI->session->userdata('workspace_id');
    $query = $CI->db->query("SELECT COUNT(id) as total FROM messages WHERE workspace_id=$workspace_id AND to_id=$user_id AND is_read=1 AND type='person'");
    $res = $query->result_array();
    
    $query1 = $CI->db->query("SELECT COUNT(id) as total FROM chat_group_members WHERE workspace_id=$workspace_id AND user_id=$user_id AND is_read=1");
    $res1 = $query1->result_array();
    
   
    return $res[0]['total']+$res1[0]['total'];
    
}

function get_chat_theme()
{
    $CI =& get_instance();
    $user_id = $CI->session->userdata('user_id');
    $CI->db->from('users');
    $CI->db->where(['id'=>$user_id]);
    $query = $CI->db->get();
    $config = $query->result_array();
 
    if(!empty($config[0]['chat_theme'])){
        return $config[0]['chat_theme'];
    }else{
        return false;
    }
}

function get_user_name()
{
    $CI =& get_instance();
    $user_id = $CI->session->userdata('user_id');
    $CI->db->from('users');
    $CI->db->where(['id'=>$user_id]);
    $query = $CI->db->get();
    $data = $query->result_array();
    if(!empty($data[0]['first_name']) && !empty($data[0]['last_name'])){
        return $data[0]['first_name'].' '.$data[0]['last_name'];
    }else{
        return false;
    }

}

function get_project_title($id)
{
    $CI =& get_instance();
    $CI->db->from('projects');
    $CI->db->where(['id'=>$id]);
    $query = $CI->db->get();
    $data = $query->result_array();
    if(!empty($data[0]['title'])){
        return $data[0]['title'];
    }else{
        return false;
    }

}

function get_project_id_by_file_id($file_id)
{
    $CI =& get_instance();
    $CI->db->from('project_media');
    $CI->db->where(['id'=>$file_id]);
    $CI->db->where(['type'=>'project']);
    $query = $CI->db->get();
    $data = $query->result_array();
    if(!empty($data[0]['type_id'])){
        return $data[0]['type_id'];
    }else{
        return false;
    }

}

function get_file_name($id)
{
    $CI =& get_instance();
    $CI->db->from('project_media');
    $CI->db->where(['id'=>$id]);
    $query = $CI->db->get();
    $data = $query->result_array();
    if(!empty($data[0]['file_name'])){
        return $data[0]['file_name'];
    }else{
        return false;
    }

}

function get_milestone_title($id)
{
    $CI =& get_instance();
    $CI->db->from('milestones');
    $CI->db->where(['id'=>$id]);
    $query = $CI->db->get();
    $data = $query->result_array();
    if(!empty($data[0]['title'])){
        return $data[0]['title'];
    }else{
        return false;
    }

}

function get_project_id_by_milestone_id($id)
{
    $CI =& get_instance();
    $CI->db->from('milestones');
    $CI->db->where(['id'=>$id]);
    $query = $CI->db->get();
    $data = $query->result_array();
    if(!empty($data[0]['project_id'])){
        return $data[0]['project_id'];
    }else{
        return false;
    }

}

function get_project_id_by_task_id($task_id)
{
    $CI =& get_instance();
    $CI->db->from('tasks');
    $CI->db->where(['id'=>$task_id]);
    $query = $CI->db->get();
    $data = $query->result_array();
    if(!empty($data[0]['project_id'])){
        return $data[0]['project_id'];
    }else{
        return false;
    }

}

function get_task_title($id)
{
    $CI =& get_instance();
    $CI->db->from('tasks');
    $CI->db->where(['id'=>$id]);
    $query = $CI->db->get();
    $data = $query->result_array();
    if(!empty($data[0]['title'])){
        return $data[0]['title'];
    }else{
        return false;
    }

}

function escape_array($array)
{
    $t = &get_instance();
    $posts = array();
    if (!empty($array)) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $posts[$key] = $t->db->escape_str($value);
            }
        } else {
            return $t->db->escape_str($array);
        }
    }
    return $posts;
}

 
?>
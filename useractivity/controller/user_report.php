<?php

namespace kaerol\useractivity\controller;

use Symfony\Component\DependencyInjection\Container;

class user_report implements report_interface
{
    /* @var Container */
    protected $phpbb_container;

    /** @var \phpbb\auth\auth */
    protected $auth;

    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\db\driver\driver */
    protected $db;

    /** @var \phpbb\controller\helper $controller_helper */
    protected $helper;

    /** @var \phpbb\notification\manager */
    protected $notification_manager;

    /** @var \phpbb\request\request */
    protected $request;

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\language\language */
    protected $language;

    /**
     * Constructor
     */

    public function __construct(
        Container $phpbb_container,
        \phpbb\auth\auth $auth,
        \phpbb\config\config $config,
        \phpbb\db\driver\driver_interface $db,
        \phpbb\controller\helper $helper,
        \phpbb\notification\manager $notification_manager,
        \phpbb\request\request $request,
        \phpbb\user $user,
        \phpbb\language\language $language
    ) {
        $this->auth = $auth;
        $this->config = $config;
        $this->helper = $helper;
        $this->db = $db;
        $this->notification_manager = $notification_manager;
        $this->request = $request;
        $this->user = $user;
        $this->user->add_lang_ext('kaerol/useractivity', 'useractivity');
        $this->language = $language;

        $this->core_users = $phpbb_container->getParameter('tables.core_users');
        $this->profile_data = $phpbb_container->getParameter('tables.profile_data');
    }

    public function user_activity_report()
    {
        $report = $this->_getUserActivity();

        $out_title_html = $this->language->lang('KAEROL_USERACTIVITY_REPORT_TITLE');
        $out_content_html = '<table border="1" width="100%" class="items_report">';
        $out_content_html .= '<tr><th rowspan="2">';
        $out_content_html .= $this->language->lang('KAEROL_USERACTIVITY_INDEX');
        $out_content_html .= '</th>';
        $out_content_html .= '<th rowspan="2">';
        $out_content_html .= $this->language->lang('KAEROL_USERACTIVITY_USER_NAME');
        $out_content_html .= '</th>';
        $out_content_html .= '<th colspan="2">';
        $out_content_html .= $this->language->lang('KAEROL_USERACTIVITY_DATE');
        $out_content_html .= '</th>';
        $out_content_html .= '</tr><tr>';
        $out_content_html .= '<th>';
        $out_content_html .= $this->language->lang('KAEROL_USERACTIVITY_REGISTER_DATE');
        $out_content_html .= '</th>';
        $out_content_html .= '<th>';
        $out_content_html .= $this->language->lang('KAEROL_USERACTIVITY_LAST_ACTIVITY_DATE');
        $out_content_html .= '</th>';
        $out_content_html .= '</tr>';
        $out_content_html .= '</th></tr>';
        $index = 1;

        foreach ($report as $row) {
            $out_content_html .= '<tr><td>';
            $out_content_html .= ($index++);
            $out_content_html .= '</td>';
            $out_content_html .= '<td>';
            $out_content_html .= $row['username'].'  ('.$row['chapter'].')';
            $out_content_html .= '</td>';
            $out_content_html .= '<td>';
            $out_content_html .= $row['regdate'];
            $out_content_html .= '</td>';
            $out_content_html .= '<td class="' . ($row['isOverWeek'] ? 'overWeek' : '') . ' ' . ($row['isOverMonth'] ? 'overMonth' : '') . ' ">';
            $out_content_html .= $row['lastvisit'];
            $out_content_html .= $row['isOverWeek'] ? '<i class="icon fa-solid fa-thumbs-down" title="' . $this->language->lang('KAEROL_USERACTIVITY_LAST_ACTIVITY_OVER_WEEK') . '"></i>' : '';
            $out_content_html .= $row['isOverMonth'] ? '<i class="icon fa-solid fa-exclamation" title="' . $this->language->lang('KAEROL_USERACTIVITY_LAST_ACTIVITY_OVER_MONTH') . '"></i>' : '';
            $out_content_html .= '</td>';
            $out_content_html .= '</td></tr>';
        }
        $out_content_html .= '</table>';

        $json_response = new \phpbb\json_response;
        $data_send = array(
            'success'           => true,
            'title'             => $out_title_html,
            'content'           => $out_content_html,
        );

        return $json_response->send($data_send);
    }

    public function _getUserActivity()
    {
        //$sql = 'SELECT u.username, u.user_lastvisit, u.user_regdate FROM ' . $this->core_users . ' u where u.user_type = 0 order by u.username asc';
        $sql = 'SELECT u.username, fd.pf_chapter, u.user_lastvisit, u.user_regdate 
                    FROM ' . $this->core_users . ' u 
                    LEFT OUTER JOIN ' . $this->profile_data . ' fd on fd.user_id = u.user_id 
                    WHERE u.user_type = 0 order by u.username asc';

        $dbResult = $this->db->sql_query($sql);
        $result = array();

        while ($row = $this->db->sql_fetchrow($dbResult)) {
            $result[] = [
                'username' => $row['username'],
                'chapter' => $row['pf_chapter'],
                'regdate' => date('Y-m-d H:i:s', $row['user_regdate']),
                'lastvisit' => date('Y-m-d H:i:s', $row['user_lastvisit']),
                'isOverWeek' =>  $row['user_lastvisit'] < strtotime('-7 days') ? true : false,
                'isOverMonth' =>  $row['user_lastvisit'] < strtotime('-30 days') ? true : false,
            ];
        }
        $this->db->sql_freeresult($dbResult);

        return $result;
    }
}

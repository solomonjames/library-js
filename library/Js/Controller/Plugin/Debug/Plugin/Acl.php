<?php

/**
 * Helpful ZFDebug tool for the ACL
 *
 * @author James Solomon <james@jmsolomon.com>
 */
class Js_Controller_Plugin_Debug_Plugin_Acl
    extends ZFDebug_Controller_Plugin_Debug_Plugin
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * @var string
     */
    protected $_identifier = "Acl";

    /**
     * @var App_Acl
     */
    protected $_acl;

    /**
     * Just gets the acl instance for later use.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_acl = Js_Acl::getInstance();
    }

    /**
     * Has to return html code for the menu tab
     *
     * @return string
     */
    public function getTab()
    {
        $aclCheckCount = count($this->_acl->getIsAlllowedBacktrace());

        $title = "ACL ({$aclCheckCount})";

        return $title;
    }

    /**
     * Has to return html code for the content panel
     *
     * @return string
     */
    public function getPanel()
    {
        $aclBacktrace = $this->_acl->getIsAlllowedBacktrace();
        $currentRole = $this->_acl->getCurrentRole();

        $html = '<h4>Access Control List</h4><br />';

        $html .= "<p><b>Current Role:</b> " . $currentRole . "</p><br />";

        $html .= "Switch User: <input id='ZFDebug_ACL_SwitchRole' type='text' name='zfdebug_contactId' value='' />";
        $html .= "<input onClick='zfdebugAcl.switchRole();' type='submit' name='zfdebug_submit' value='Go' /> <small>(supply contact_id)</small> <br /><br />";

        $html .= "isAllowed - Resource: <input id='ZFDebug_ACL_isAllowed_Resource' type='text' name='zfdebug_isallowed_resource' value='' />";
        $html .= "Privilege: <input id='ZFDebug_ACL_isAllowed_Privilege' type='text' name='zfdebug_isallowed_privilege' value='' />";
        $html .= "Group ID (optional): <input id='ZFDebug_ACL_isAllowed_Group_Id' type='text' name='zfdebug_isallowed_group_id' value='' />";
        $html .= "<input onClick='zfdebugAcl.isAllowed();' type='submit' name='zfdebug_isallowed_submit' value='Test' /> ";
        $html .= "<input onClick='zfdebugAcl.addRule(\"allow\");' type='submit' name='zfdebug_addallow_submit' value='Add Allow' /> ";
        $html .= "<input onClick='zfdebugAcl.addRule(\"deny\");' type='submit' name='zfdebug_adddeny_submit' value='Add Deny' /> ";

        $html .= "<br /><br />";
        $html .= "<h4>isAllowed Backtrace</h4>";
        $html .= "<ol>";
        foreach ($aclBacktrace as $k => $a) {
            $html .= "<li>{$k} : [<b>Resource:</b> {$a['resource']}] [<b>Privilege:</b> {$a['privilege']}]</li>";
        }
        $html .= "</ol>";

        $html .= "<div id='ZFDebug_ACL_SwitchRole_Modal' style='display: none;'>Logging in as contact_id : </div>";

        $html .= $this->_addJs();

        return $html;
    }

    /**
     * All of the JS needed for the tab
     *
     * @return string
     */
    protected function _addJs()
    {
        $js = "
            <script type='text/javascript'>
                var zfdebugAcl = (function(){
                    var that = {};

                    that.switchRole = function() {
                        var contactId = $('#ZFDebug_ACL_SwitchRole').val();

                        jQuery('#ZFDebug_ACL_SwitchRole_Modal').append(contactId);

                        jQuery('#ZFDebug_ACL_SwitchRole_Modal').dialog({
                            modal: true,
                            open: function(event, ui) {
                                jQuery.getJSON('/debug/acl.switch?contactId=' + contactId,
                                    function(data) {
                                        if (data.success) {
                                            jQuery('#ZFDebug_ACL_SwitchRole_Modal').html('Currently refreshing the page for changes to take effect');
                                            location.reload(true);
                                        } else {
                                            jQuery('#ZFDebug_ACL_SwitchRole_Modal').html('There was an error, sorry...');
                                        }
                                    }
                                );
                            }
                        });
                    };

                    that.isAllowed = function() {
                        var resource = jQuery('#ZFDebug_ACL_isAllowed_Resource').val();
                        var privilege = jQuery('#ZFDebug_ACL_isAllowed_Privilege').val();

                        var url = '/debug/acl.isallowed?resource=' + resource + '&privilege=' + privilege;
                        jQuery.getJSON(url,
                            function(data) {
                                if (data.success) {
                                    alert(data.messages[0]);
                                }
                            }
                        );
                    };

                    that.addRule = function(type) {
                        var resource = jQuery('#ZFDebug_ACL_isAllowed_Resource').val();
                        var privilege = jQuery('#ZFDebug_ACL_isAllowed_Privilege').val();
                        var group_id = jQuery('#ZFDebug_ACL_isAllowed_Group_Id').val();

                        var url = '/debug/acl.add' + type + '?resource=' + resource + '&privilege=' + privilege + '&group_id=' + group_id;
                        jQuery.getJSON(url,
                            function(data) {
                                if (data.success) {
                                    alert(data.messages[0]);
                                }
                            }
                        );
                    };

                    return that;
                })();
            </script>
        ";

        return $js;
    }

    /**
     * Has to return a unique identifier for the specific plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }
}
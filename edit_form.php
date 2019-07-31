<?php
// This file is part of SAML2 Authentication Plugin for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * IdP edit settings form.
 *
 * @package     auth_saml2
 * @author      Jackson D'souza <jackson.dsouza@catalyst-eu.net>
 * @copyright   2019 Catalyst IT Europe {@link http://www.catalyst-eu.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_saml2\admin\saml2_settings;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The IdP edit form class.
 */
class auth_saml2_idp_edit_form extends moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        $id = isset($this->_customdata['id']) ? $this->_customdata['id'] : false;

        $mform->addElement('selectyesno', 'idpmetadatarefresh', get_string('idpmetadatarefresh', 'auth_saml2'));
        $mform->addElement('static', 'idpmetadatarefresh_help',
                            null,
                            get_string('idpmetadatarefresh_help', 'auth_saml2'));

        $mform->addElement('selectyesno', 'showidplink', get_string('showidplink', 'auth_saml2'));
        $mform->addElement('static', 'showidplink_help',
                            null,
                            get_string('showidplink_help', 'auth_saml2'));
        $mform->setDefault('showidplink', 1);

        // See section 8.3 from http://docs.oasis-open.org/security/saml/v2.0/saml-core-2.0-os.pdf for more information.
        $nameidlist = [
            'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
            'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
            'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName',
            'urn:oasis:names:tc:SAML:1.1:nameid-format:WindowsDomainQualifiedName',
            'urn:oasis:names:tc:SAML:2.0:nameid-format:kerberos',
            'urn:oasis:names:tc:SAML:2.0:nameid-format:entity',
            'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
        ];
        // Create keyed array so options get saved properly.
        $nameidlistkeyed = [];
        foreach ($nameidlist as $nameid) {
            $nameidlistkeyed[$nameid] = $nameid;
        }
        $mform->addElement('select', 'nameidpolicy', get_string('nameidpolicy', 'auth_saml2'), $nameidlistkeyed);
        $mform->addElement('static', 'nameidpolicy_help',
                            null,
                            get_string('nameidpolicy_help', 'auth_saml2'));

        $mform->addElement('selectyesno', 'nameidasattrib', get_string('nameidasattrib', 'auth_saml2'));
        $mform->addElement('static', 'nameidasattrib_help',
                            null,
                            get_string('nameidasattrib_help', 'auth_saml2'));

        $mform->addElement('text', 'idpattr', get_string('idpattr', 'auth_saml2'), array('size' => 40, 'maxlength' => 50));
        $mform->setType('idpattr', PARAM_TEXT);
        $mform->addElement('static', 'idpattr_help',
                            null,
                            get_string('idpattr_help', 'auth_saml2'));

        // Moodle Field.
        $fields = [
            'username'      => get_string('username'),
            'email'         => get_string('email')
        ];
        $mform->addElement('select', 'mdlattr', get_string('mdlattr', 'auth_saml2'), $fields);
        $mform->addElement('static', 'mdlattr_help',
                            null,
                            get_string('mdlattr_help', 'auth_saml2'));
        $mform->setDefault('mdlattr', 'username');

        $mform->addElement('selectyesno', 'tolower', get_string('tolower', 'auth_saml2'));
        $mform->addElement('static', 'tolower_help',
                            null,
                            get_string('tolower_help', 'auth_saml2'));

        $mform->addElement('selectyesno', 'autocreate', get_string('autocreate', 'auth_saml2'));
        $mform->addElement('static', 'autocreate_help',
                            null,
                            get_string('autocreate_help', 'auth_saml2'));

        $mform->addElement('text', 'alterlogout', get_string('alterlogout', 'auth_saml2'), array('size' => 40, 'maxlength' => 50));
        $mform->setType('alterlogout', PARAM_URL);
        $mform->addElement('static', 'alterlogout_help',
                            null,
                            get_string('alterlogout_help', 'auth_saml2'));

        $authplugin = get_auth_plugin('saml2');
        $mform->addElement('static', 'sspversion',
                            get_string('sspversion', 'auth_saml2'),
                            $authplugin->get_ssp_version());

        // Display locking / mapping of profile fields.
        $help = get_string('auth_updatelocal_expl', 'auth');
        $help .= get_string('auth_fieldlock_expl', 'auth');
        $help .= get_string('auth_updateremote_expl', 'auth');
        auth_saml2_display_auth_lock_options(
            $mform,
            $authplugin->authtype,
            $authplugin->userfields,
            $help,
            true,
            true,
            $authplugin->get_custom_user_profile_fields(),
            'form'
        );

        if ($id !== false) {
            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);
        }

        $this->add_action_buttons();

    }

    /**
     * Definition after data.
     *
     * @return void
     */
    public function definition_after_data() {
        $mform =& $this->_form;
        foreach ($this->_customdata['data'] as $key => $value) {
            $mform->setDefault($key, $value);
        }
    }
}

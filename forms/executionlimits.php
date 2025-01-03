<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form to set execution limits
 *
 * @package mod_vpl
 * @copyright 2012 onwards Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../locallib.php');
require_once(dirname(__FILE__).'/../vpl.class.php');
global $CFG;
require_once($CFG->libdir.'/formslib.php');

class mod_vpl_executionlimits_form extends moodleform {
    protected $vpl;
    public function __construct($page, $vpl) {
        $this->vpl = $vpl;
        parent::__construct( $page );
    }
    protected function definition() {
        $plugincfg = get_config('mod_vpl');
        $mform = & $this->_form;
        $id = $this->vpl->get_course_module()->id;
        $instance = $this->vpl->get_instance();
        $mform->addElement( 'hidden', 'id', $id );
        $mform->setType( 'id', PARAM_INT );
        $mform->addElement( 'header', 'header_execution_limits', get_string( 'resourcelimits', VPL ) );

        $settings = [
                'exetime' => vpl_get_select_time( ( int ) $plugincfg->maxexetime ),
                'exememory' => vpl_get_select_sizes( 16 * 1024 * 1024, ( int ) $plugincfg->maxexememory ),
                'exefilesize' => vpl_get_select_sizes( 1024 * 256, ( int ) $plugincfg->maxexefilesize ),
        ];
        foreach ($settings as $name => $options) {
            $inheritedlimit = $instance->basedon ? self::get_closest_set_execution_limit($instance->basedon, 'max' . $name) : 0;
            $defaultvaluestring = trim($options[ vpl_get_array_key($options, $inheritedlimit ?: $plugincfg->{'default' . $name}) ]);
            if ($inheritedlimit) {
                $defaultvaluestring = get_string('inherit', VPL) . ' (' . $defaultvaluestring . ')';
            } else {
                $defaultvaluestring = get_string('default') . ' (' . $defaultvaluestring . ')';
            }
            self::add_resource_limit_select($mform, 'max' . $name, get_string( 'max' . $name, VPL ),
                    $options, $defaultvaluestring, $instance->{'max' . $name});
        }

        $mform->addElement( 'text', 'maxexeprocesses', get_string( 'maxexeprocesses', VPL ) );
        $mform->setType( 'maxexeprocesses', PARAM_INT );
        if ($instance->maxexeprocesses) {
            $mform->setDefault( 'maxexeprocesses', $instance->maxexeprocesses );
        }
        $mform->addElement( 'submit', 'savelimitoptions', get_string( 'saveoptions', VPL ) );
    }

    /**
     * Adds a select element to the resource limits form.
     * @param MoodleQuickForm $mform The form to which the element will be added.
     * @param string $name The name of the element.
     * @param string $label The label of the element.
     * @param array $selectoptions The selectable options of the element.
     *  The [0] => 'select' option will be replaced by a localized string describing the default value.
     * @param int $defaultvalue The default value to use when no other value is selected.
     * @param int $currentvalue The value to which the element should be set when displaying the form.
     */
    private static function add_resource_limit_select($mform, $name, $label, $selectoptions, $defaultvaluestring, $currentvalue) {
        $selectoptions[0] = $defaultvaluestring;
        $mform->addElement( 'select', $name, $label, $selectoptions );
        $mform->setType( $name, PARAM_INT );
        if ($currentvalue) {
            $mform->setDefault( $name, $currentvalue );
        }
    }

    /**
     * Retrieve the first non-empty setting in the basedon chain.
     * @param number $instanceid ID in 'vpl' table
     * @param string $field Setting name (DB column name)
     */
    private static function get_closest_set_execution_limit($instanceid, $field) {
        global $DB;
        $vplinstance = $DB->get_record('vpl', [ 'id' => $instanceid ]);
        if ($vplinstance->{$field}) {
            return $vplinstance->{$field};
        } else if ($vplinstance->basedon) {
            return self::get_closest_set_execution_limit($vplinstance->basedon, $field);
        } else {
            return 0;
        }
    }
}

require_login();

$id = required_param( 'id', PARAM_INT );
$vpl = new mod_vpl( $id );
$vpl->prepare_page( 'forms/executionlimits.php', [
        'id' => $id,
] );
vpl_include_jsfile( 'hideshow.js' );
$vpl->require_capability( VPL_MANAGE_CAPABILITY );
// Display page.
$vpl->print_header( get_string( 'resourcelimits', VPL ) );
$vpl->print_heading_with_help( 'resourcelimits' );

$mform = new mod_vpl_executionlimits_form( 'executionlimits.php', $vpl );
if ($fromform = $mform->get_data()) {
    if (isset( $fromform->savelimitoptions )) {
        $instance = $vpl->get_instance();
        \mod_vpl\event\vpl_execution_limits_updated::log( $vpl );
        $instance->maxexetime = $fromform->maxexetime;
        $instance->maxexememory = $fromform->maxexememory;
        $instance->maxexefilesize = $fromform->maxexefilesize;
        $instance->maxexeprocesses = $fromform->maxexeprocesses;
        if ( $vpl->update() ) {
            vpl_notice( get_string( 'optionssaved', VPL ) );
        } else {
            vpl_notice( get_string( 'optionsnotsaved', VPL ), 'error' );
        }
    }
}
\mod_vpl\event\vpl_execution_limits_viewed::log( $vpl );
$mform->display();
$vpl->print_footer();

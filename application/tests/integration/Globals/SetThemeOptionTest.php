<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 **/

/**
 * Test set_theme_option().
 *
 * @package Omeka
 * @copyright Center for History and New Media, 2007-2010
 **/
class Globals_SetThemeOptionTest extends PHPUnit_Framework_TestCase
{   
    const THEME = 'foobar';
    const THEME_OPTIONS_OPTION = 'theme_foobar_options';
    
    private $_themeOptions = array(
        'logo' => 'foobar.jpg',
        'show_title' => true,
        'show_description' => false
    );
    
    public function setUp()
    {
        Omeka_Context::getInstance()->setOptions(array(
            Theme::PUBLIC_THEME_OPTION => self::THEME,
            self::THEME_OPTIONS_OPTION => serialize($this->_themeOptions)
        ));
        
        $this->db = $this->getMock('Omeka_Db', array(), array(), '', false);
        $this->db->Option = 'omeka_options';
        
        Omeka_Context::getInstance()->setDb($this->db);
    }
    
    public function testDatabaseInteraction()
    {
        $expectedThemeOptions = $this->_themeOptions;
        $expectedThemeOptions['logo'] = 'bazdoo.png';
        $this->db->expects($this->once())
                 ->method('exec')
                 ->with('REPLACE INTO omeka_options (name, value) VALUES (?, ?)',
                        array(self::THEME_OPTIONS_OPTION, serialize($expectedThemeOptions)));
        set_theme_option('logo', 'bazdoo.png');        
    }
    
    public function testWithoutThemeName()
    {   
        set_theme_option('logo', 'whackadoo.png');
        $this->assertEquals('whackadoo.png', get_theme_option('logo'));
    }
    
    public function testWithThemeName()
    {
        set_theme_option('logo', 'jammy.png', 'whatever');
        $this->assertEquals('jammy.png', get_theme_option('logo', 'whatever'));
        set_theme_option('new_option', 'hoohas', 'whatever');
        $this->assertEquals('hoohas', get_theme_option('new_option', 'whatever'));
    }    
}

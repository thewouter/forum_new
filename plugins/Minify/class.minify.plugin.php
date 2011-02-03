<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

// Define the plugin:
$PluginInfo['Minify'] = array(
   'Name' => 'Minify',
   'Description' => 'Analyzes each page request for js & css files, merging and minifying them where applicable.',
   'Version' => '1.0.1b',
   'Author' => "Mark O'Sullivan",
   'AuthorEmail' => 'mark@vanillaforums.com',
   'AuthorUrl' => 'http://markosullivan.ca'
);

class MinifyPlugin extends Gdn_Plugin {
   
   protected $BasePath = "";

   public function Setup() {
      $Folder = PATH_LOCAL_CACHE . DS . 'Minify';
      if (!file_exists($Folder))
         @mkdir($Folder);
   }
   
   // Empty the cache when disabling this plugin, enabling or disabling any plugin, application, or theme
   public function OnDisable() { $this->_EmptyCache(); }
   public function SettingsController_AfterEnablePlugin_Handler() { $this->_EmptyCache(); }
   public function SettingsController_AfterDisablePlugin_Handler() { $this->_EmptyCache(); }
   public function SettingsController_AfterEnableApplication_Handler() { $this->_EmptyCache(); }
   public function SettingsController_AfterDisableApplication_Handler() { $this->_EmptyCache(); }
   public function SettingsController_AfterEnableTheme_Handler() { $this->_EmptyCache(); }
   
   private function _EmptyCache() {
      $Files = glob(PATH_LOCAL_CACHE.'/Minify/*', GLOB_MARK);
      foreach ($Files as $File) {
         if (substr($File, -1) != '/')
            unlink($File);
      }
   }

   /**
    *
    * @param HeadModule $Head
    */
   public function HeadModule_BeforeToString_Handler($Head) {
      $Tags = $Head->Tags();

      // Grab all of the css.
      $CssToCache = array();
      $JsToCache = array(); // Add the global js files
      $GlobalJS = array(
         'jquery.js',
         'jquery.livequery.js',
         'jquery.form.js',
         'jquery.popup.js',
         'jquery.gardenhandleajaxform.js',
         'global.js'
      );

      foreach ($Tags as $Index => $Tag) {
         $IsJs = GetValue(HeadModule::TAG_KEY, $Tag) == 'script';
         $IsCss = (GetValue(HeadModule::TAG_KEY, $Tag) == 'link' && GetValue('rel', $Tag) == 'stylesheet');
         if (!$IsJs && !$IsCss)
            continue;

         if ($IsCss)
            $Href = GetValue('href', $Tag, '!');
         else
            $Href = GetValue('src', $Tag, '!');

         if ($Href[0] != '/')
            continue;

         // Strip any querystring off the href.
         $Href = preg_replace('`\?.*`', '', $Href);

         $Path = PATH_ROOT.$Href;

         if (!file_exists($Path))
            continue;

         // Remove the css from the tag because minifier is taking care of it.
         unset($Tags[$Index]);

         // Add the reference to the appropriate cache collection.
         if ($IsCss) {
            $CssToCache[] = $Href;
         } elseif ($IsJs) {
            // Don't include the file if it's in the global js.
            $Filename = basename($Path);
            if (in_array($Filename, $GlobalJS)) {
               $GlobalJsFound = TRUE;
               continue;
            }
            $JsToCache[] = $Href;
         }
      }
      
      // Add minified css & js directly to the head module.
      $Url = 'plugins/Minify/min/?';
      $query = array();

      $Head->Tags($Tags);
      $this->BasePath = Gdn::Request()->WebRoot();

      $Head->AddCss($Url . 'token=' . $this->_PrepareToken($CssToCache), 'screen');

      $Head->AddScript($Url . ($BasePath!='' ? "b=$BasePath&amp;" : '')
			. 'g=globaljs', 'text/javascript', -100);

      $Head->AddScript($Url . 'token=' . $this->_PrepareToken($JsToCache));
   }
   
   protected function _PrepareToken($Files) {
      $Query = array('f' => implode(',', array_unique($Files)));
      if ($this->BasePath != '')
	 $Query['b'] = $BasePath;
      $Query = serialize($Query);
      $token = md5($Query);
      // save file
      $CacheFile = PATH_LOCAL_CACHE . DS . 'Minify' . DS . 'query_' . $token;
      if (!file_exists($CacheFile)) {
	 file_put_contents($CacheFile, $Query);
      }
      return $token;
   }
}
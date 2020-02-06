<?php if (!defined('APPLICATION')) exit();

// Conversations
$Configuration['Conversations']['Version'] = '2.3';

// Database
$Configuration['Database']['Name'] = 'radixenschede_database';
$Configuration['Database']['Host'] = 'localhost';
$Configuration['Database']['User'] = 'radixenschede_478_rw';
$Configuration['Database']['Password'] = 'TMR9SKlxTH9z';

// EnabledApplications
$Configuration['EnabledApplications']['Conversations'] = 'conversations';
$Configuration['EnabledApplications']['Vanilla'] = 'vanilla';

// EnabledLocales
$Configuration['EnabledLocales']['Dutch'] = 'nl';

// EnabledPlugins
$Configuration['EnabledPlugins']['GettingStarted'] = false;
$Configuration['EnabledPlugins']['HtmLawed'] = 'HtmLawed';
$Configuration['EnabledPlugins']['DiscussionPolls'] = true;
$Configuration['EnabledPlugins']['cleditor'] = false;
$Configuration['EnabledPlugins']['ButtonBar'] = false;
$Configuration['EnabledPlugins']['Emotify'] = false;
$Configuration['EnabledPlugins']['FileUpload'] = false;
$Configuration['EnabledPlugins']['editor'] = true;
$Configuration['EnabledPlugins']['EmojiExtender'] = true;
$Configuration['EnabledPlugins']['PrivateCommunity'] = true;
$Configuration['EnabledPlugins']['Debugger'] = false;
$Configuration['EnabledPlugins']['Gravatar'] = true;

// Garden
$Configuration['Garden']['Errors']['LogEnabled'] = true;
$Configuration['Garden']['Errors']['LogFile'] = 'log/DebugLog';
$Configuration['Garden']['Title'] = 'Forum Radix Enschede';
$Configuration['Garden']['Cookie']['Salt'] = 'I66UxmPg4mlhih76';
$Configuration['Garden']['Cookie']['Domain'] = '';
$Configuration['Garden']['Registration']['ConfirmEmail'] = false;
$Configuration['Garden']['Registration']['Method'] = 'Approval';
$Configuration['Garden']['Registration']['CaptchaPrivateKey'] = '6LdQWTgUAAAAADkpf5F-iAdeY0GBE-gWVG0ssWyv';
$Configuration['Garden']['Registration']['CaptchaPublicKey'] = '6LdQWTgUAAAAAO0tILcZkPY4TIyRD8w8hNjotlP3';
$Configuration['Garden']['Registration']['InviteExpiration'] = '1 week';
$Configuration['Garden']['Registration']['InviteRoles']['3'] = '0';
$Configuration['Garden']['Registration']['InviteRoles']['4'] = '0';
$Configuration['Garden']['Registration']['InviteRoles']['8'] = '0';
$Configuration['Garden']['Registration']['InviteRoles']['16'] = '0';
$Configuration['Garden']['Registration']['InviteRoles']['32'] = '0';
$Configuration['Garden']['Email']['SupportName'] = 'no-reply@radixenschede.nl';
$Configuration['Garden']['Email']['Format'] = 'text';
$Configuration['Garden']['Email']['SupportAddress'] = 'no-reply@radixenschede.nl';
$Configuration['Garden']['Email']['UseSmtp'] = '1';
$Configuration['Garden']['Email']['SmtpHost'] = 'smtp.utwente.nl';
$Configuration['Garden']['Email']['SmtpUser'] = '';
$Configuration['Garden']['Email']['SmtpPassword'] = '';
$Configuration['Garden']['Email']['SmtpPort'] = '25';
$Configuration['Garden']['Email']['SmtpSecurity'] = '';
$Configuration['Garden']['SystemUserID'] = '1';
$Configuration['Garden']['InputFormatter'] = 'BBCode';
$Configuration['Garden']['Version'] = '2.2';
$Configuration['Garden']['Cdns']['Disable'] = false;
$Configuration['Garden']['CanProcessImages'] = true;
$Configuration['Garden']['Installed'] = true;
$Configuration['Garden']['InstallationID'] = '6EDD-0FA05F0F-372CB202';
$Configuration['Garden']['InstallationSecret'] = '819bda928b1f9b992edde3eafcd7dc9da9b7b4e7';
$Configuration['Garden']['Theme'] = 'Gopi';
$Configuration['Garden']['ThemeOptions']['Name'] = 'Gopi';
$Configuration['Garden']['ThemeOptions']['Styles']['Key'] = 'Carrot';
$Configuration['Garden']['ThemeOptions']['Styles']['Value'] = '%s_carrot';
$Configuration['Garden']['MobileTheme'] = 'Gopi';
$Configuration['Garden']['HomepageTitle'] = 'Forum Radix Enschede';
$Configuration['Garden']['Description'] = '';
$Configuration['Garden']['ShareImage'] = 'IYX74JHHEEI5.png';
$Configuration['Garden']['MobileInputFormatter'] = 'BBCode';
$Configuration['Garden']['AllowFileUploads'] = true;
$Configuration['Garden']['EmojiSet'] = 'twitter';
$Configuration['Garden']['PrivateCommunity'] = true;
$Configuration['Garden']['Locale'] = 'nl';

// Plugins
$Configuration['Plugins']['GettingStarted']['Dashboard'] = '1';
$Configuration['Plugins']['GettingStarted']['Plugins'] = '1';
$Configuration['Plugins']['GettingStarted']['Discussion'] = '1';
$Configuration['Plugins']['GettingStarted']['Categories'] = '1';
$Configuration['Plugins']['GettingStarted']['Registration'] = '1';
$Configuration['Plugins']['GettingStarted']['Profile'] = '1';
$Configuration['Plugins']['DiscussionEvent']['DisplayInSidepanel'] = true;
$Configuration['Plugins']['DiscussionEvent']['MaxDiscussionEvents'] = 10;
$Configuration['Plugins']['editor']['ForceWysiwyg'] = '';

// Vanilla
$Configuration['Vanilla']['Version'] = '2.3';

// Last edited by wouter (0.0.0.2)2019-12-17 19:54:15
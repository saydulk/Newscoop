#!/usr/bin/env php
<?php
/**
 * @package Newscoop
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
$newscoopDir = realpath(__DIR__ . '/../');
// set chmods for directories
exec('chmod -R 775 '.$newscoopDir.'/cache/');
exec('chmod -R 775 '.$newscoopDir.'/log/');
exec('chmod -R 775 '.$newscoopDir.'/conf/');
exec('chmod -R 775 '.$newscoopDir.'/library/Proxy/');
exec('chmod -R 775 '.$newscoopDir.'/themes/');
exec('chmod -R 775 '.$newscoopDir.'/plugins/');
exec('chmod -R 775 '.$newscoopDir.'/public/');
exec('chmod -R 775 '.$newscoopDir.'/images/');

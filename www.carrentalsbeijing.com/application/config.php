<?php
$config = array();
/* production */
$config['production']['db']['adapter'] = 'Mysqli';
$config['production']['db']['params']['host'] = 'rdszazvamy3qvmb.mysql.rds.aliyuncs.com';
$config['production']['db']['params']['port'] = 3306;
$config['production']['db']['params']['username'] = 'robert';
$config['production']['db']['params']['password'] = 'robertbj';
$config['production']['db']['params']['dbname'] = 'car-rent';
$config['production']['db']['params']['default'] = true;
$config['production']['db']['params']['persistent'] = true;
$config['production']['db']['params']['charset'] = 'utf8';
$config['production']['db']['params']['options']['autoQuoteIdentifiers'] = true;

$config['production']['crypt_key'] = 'DY1GcIH2AAklzeHj9vocETuMDXZun8DBfn75rPy5Al699nQfy589uAN8';
$config['production']['crypt_key2'] = 'KpHska2GsQxd4bz3a1fIaFf7uHg4JBgYK06ETzg5kWMQrofIJUhfWIzu';

$config['production']['controller_plugins'] = array();

$config['production']['mail']['smtp_host'] = 'smtp-mail.outlook.com';
$config['production']['mail']['smtp_port'] = 587;
$config['production']['mail']['smtp_secure'] = 'tls';
$config['production']['mail']['smtp_user'] = 'carrentalsbeijing@outlook.com';
$config['production']['mail']['smtp_password'] = 'Robertzhou666';
$config['production']['order_mail_recipients'] = 'zhiyi@foxmail.com,13811222052@139.com'; //多个地址逗号分隔

/* development */
$config['development'] = $config['production'];
$config['development']['db']['params']['host'] = 'localhost';
$config['development']['db']['params']['port'] = 3306;
$config['development']['db']['params']['socket'] = '/tmp/mysql.sock';
$config['development']['db']['params']['username'] = 'root';
$config['development']['db']['params']['password'] = '';
$config['development']['db']['params']['dbname'] = 'robert';

/* testing */
$config['testing'] = $config['production'];
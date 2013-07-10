ElasticTranscoderPHP
====================

PHP class for interacting with Amazon Elastic Transcoder.

#### Creating a new transcoding job ###

```php
<?php

require('ElasticTranscoder3.php');

$presetId = 'presetId';
$pipelineId = 'pipelineId';

$input = array('Key' => 'inputFile');
$output = array(
  'Key' => 'outputFile.mp4',
  'PresetId' => $presetId
 );

AWS_ET::setAuth('awsAccessKey', 'awsPrivateKey');

$result = AWS_ET::createJob(
  $input,
  array(
    $output
  ),
  $pipelineId
);

if (!$result) {
  echo AWS_ET::getErrorMsg();
} else {
  echo 'New job ID: '.$result['Job']['Id'];
}

```
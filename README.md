ElasticTranscoderPHP
====================

PHP class for interacting with Amazon Elastic Transcoder.

#### Creating a new transcoding job ###

```php
<?php

require('ElasticTranscoder3.php');

$inputKey = 'inFile';
$outputKey = 'outFile.mp4';
$presetId = 'presetId';
$pipelineId = 'pipelineId';

AWS_ET::setAuth('awsAccessKey', 'awsPrivateKey');

$result = AWS_ET::createJob(
  array(
    'Key' => $inputKey
  ),
  array(
    array(
    'Key' => $outputKey,
    'PresetId' => $presetId
    )
  ),
  $pipelineId
);

if (!$result) {
  echo AWS_ET::getErrorMsg();
} else {
  echo 'New job ID: '.$result['Job']['Id'];
}

```
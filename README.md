ElasticTranscoderPHP
====================

PHP class for interacting with Amazon Elastic Transcoder.

Check out <a href="http://docs.aws.amazon.com/elastictranscoder/latest/developerguide/getting-started.html">Getting Started with Elastic Transcoder</a> for a good introduction to the service.

#### Creating a transcoding job ###

```php
<?php

require('ElasticTranscoder.php');

AWS_ET::setAuth('awsAccessKey', 'awsPrivateKey'); // Set AWS credentials

$pipelineId = 'pipelineId';
$input = array('Key' => 'inputFile');
$output = array(
  'Key' => 'outputFile.mp4',
  'PresetId' => 'presetId'
 );

$result = AWS_ET::createJob($input, array($output), $pipelineId);

if (!$result) {
  echo AWS_ET::getErrorMsg();
} else {
  echo 'New job ID: ' . $result['Job']['Id'];
}

?>
```
ElasticTranscoderPHP
====================

PHP class for interacting with Amazon Elastic Transcoder.

<strong>More Information:</strong><br />
<a href="http://docs.aws.amazon.com/elastictranscoder/latest/developerguide/getting-started.html">Getting Started with Elastic Transcoder</a>

#### Usage ###

Object-oriented method:

```php
$et = new AWS_ET($awsAccessKey, $awsSecretKey);
```

Statically:

```php
AWS_ET::setAuth($awsAccessKey, $awsSecretKey);
```

#### Job Operations ####

Creating a new transcoding job:

```php
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
```

List jobs by pipeline:

```php
AWS_ET::listJobsByPipeline( string $pipelineId [, $ascending = true ] );
```

List jobs by status:

```php
AWS_ET::listJobsByPipeline( string $status );
```

Get job info:

```php
AWS_ET::readJobs( string $jobId );
```

Cancel a job:

```php
AWS_ET::cancelJobs( string $jobId );
```

#### License ####

Released under the MIT license.

[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/429c074cf07de7bee3ca6af902cd8141 "githalytics.com")](http://githalytics.com/LPology/ElasticTranscoderPHP)
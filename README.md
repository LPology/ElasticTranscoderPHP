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
AWS_ET::listJobsByPipeline( string $pipelineId [, boolean $ascending = true ] );
```

List jobs by status:

```php
AWS_ET::listJobsByPipeline( string $status );
```

Get job info:

```php
AWS_ET::readJob( string $jobId );
```

Cancel a job:

```php
AWS_ET::cancelJob( string $jobId );
```

#### Pipeline Operations ####

Create a new pipeline:

```php
AWS_ET::createPipeline( string $name, string $inputBucket, string $outputBucket, string $role [, array $notifications ] );
```

Get a list pipelines:

```php
AWS_ET::listPipelines();
```

Get info about a pipeline:

```php
AWS_ET::readPipeline( string $pipelineId );
```

Update pipeline settings:

```php
AWS_ET::updatePipeline( string $pipelineId, array $updates );
```

Change the status of a pipeline (active/paused):

```php
AWS_ET::updatePipelineStatus( string $pipelineId, string $status );
```

Update pipeline notification settings:

```php
AWS_ET::updatePipelineNotifications( string $pipelineId, array $notifications );
```

Delete a pipeline:

```php
AWS_ET::deletePipeline( string $pipelineId );
```

Test the settings for a pipeline:

```php
AWS_ET::testRole( string $inputBucket, string $outputBucket, string $role, array $topics );
```

#### Preset Operations ####

Create a preset:

```php
AWS_ET::createPreset( string $name, string $description [, string $container = 'mp4' ] [, array $audio ] [, array $video ] [, array $thumbnails ]);
```

List all presets:

```php
AWS_ET::listPresets();
```

Get info about a preset:

```php
AWS_ET::readPreset( string $presetId );
```

Delete a preset:

```php
AWS_ET::deletePreset( string $presetId );
```

#### Misc. ####

Set AWS authentication credentials:

```php
AWS_ET::setAuth( string $awsAccessKey, string $awsSecretKey );
```

Set AWS region:

```php
AWS_ET::setRegion( string $region = 'us-east-1' );
```

Get HTTP status code of server response:

```php
AWS_ET::getStatusCode();
```

Get server response:

```php
AWS_ET::getResponse();
```

Get error message, if any:

```php
AWS_ET::getErrorMsg();
```

#### License ####

Released under the MIT license.

[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/429c074cf07de7bee3ca6af902cd8141 "githalytics.com")](http://githalytics.com/LPology/ElasticTranscoderPHP)
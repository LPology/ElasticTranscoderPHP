<?php
/**
 * Amazon Elastic Transcoder class
 * Version 1.0.2
 * https://github.com/LPology/ElasticTranscoderPHP
 *
 * Copyright 2013 LPology, LLC
 * Released under the MIT license
 */

class AWS_ET {

  private static $AwsAccessKey;
  private static $AwsPrivateKey;
  private static $Region = 'us-east-1';
  private static $Date;
  private static $RequestBody;
  private static $HttpRequestMethod;
  private static $Uri;
  private static $Headers;
  private static $ResponseStatus;
  private static $Response;
  private static $ErrorMsg;

  public function __construct ($awsAccessKey = null, $awsPrivateKey = null, $region = null) {
    if ($awsAccessKey !== null && $awsPrivateKey !== null) {
      self::setAuth($awsAccessKey, $awsPrivateKey);
    }
    if ($region !== null) {
      self::setRegion($region);
    }
  }

  /**
  * Create a new transcoding job
  *
  * @param array $Input file input settings
  * @param array $Outputs file output settings
  * @param string $PipelineId pipelineId
  * @param string $OutputKeyPrefix prefix for file names
  * @param array $Playlists info about master playlist
  * @return array | false
  */
  public static function createJob($input, $outputs, $pipelineId, $outputKeyPrefix = null, $playlists = array(), $usermetadata = array()) {
    self::resetProps();
    self::$HttpRequestMethod = 'POST';
    self::$Uri = '/2012-09-25/jobs';
    $requestBody = array(
        'Input' => array(
            'Key' => $input['Key'],
            'FrameRate' => (array_key_exists('FrameRate', $input)) ? $input['FrameRate'] : 'auto',
            'Resolution' => (array_key_exists('Resolution', $input)) ? $input['Resolution'] : 'auto',
            'AspectRatio' => (array_key_exists('AspectRatio', $input)) ? $input['AspectRatio'] : 'auto',
            'Interlaced' => (array_key_exists('Interlaced', $input)) ? $input['Interlaced'] : 'auto',
            'Container' => (array_key_exists('Container', $input)) ? $input['Container'] : 'auto'
          )
       );
    if ($outputKeyPrefix !== null) {
      $requestBody['OutputKeyPrefix'] = $outputKeyPrefix;
    }
    $requestBody['Outputs'] = array();
    $num = sizeof($outputs);
    for ($i=0; $i<$num; $i++) {
      $requestBody['Outputs'][$i] = array(
          'Key' => $outputs[$i]['Key'],
          'ThumbnailPattern' => (array_key_exists('ThumbnailPattern', $outputs[$i])) ? $outputs[$i]['ThumbnailPattern'] : '',
          'Rotate' => (array_key_exists('Rotate', $outputs[$i])) ? $outputs[$i]['Rotate'] : 'auto',
          'PresetId' => $outputs[$i]['PresetId']
        );
      if (array_key_exists('SegmentDuration', $outputs[$i])) {
        $requestBody['Outputs'][$i]['SegmentDuration'] = $outputs[$i]['SegmentDuration'];
      }
    }
    if (!empty($playlists)) {
      $requestBody['Playlists'] = array(
          'Format' => 'HLSv3',
          'Name' => $playlists['Name']
      );
      if (array_key_exists('OutputKeys', $playlists)) {
        $requestBody['Playlists']['OutputKeys'] = $playlists['OutputKeys'];
      }
    }
    if (!empty($usermetadata)) {
      $requestBody['UserMetadata'] = $usermetadata;
    }
    $requestBody['PipelineId'] = $pipelineId;
    $requestBody = json_encode($requestBody);
    self::setRequestBody($requestBody);
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Get all jobs for a pipeline
  *
  * @param string $pipelineId pipeline ID
  * @param boolean $ascending results in ascending order
  * @return array | false
  */
  public static function listJobsByPipeline($pipelineId, $ascending = true) {
    self::resetProps();
    self::$HttpRequestMethod = 'GET';
    if ($ascending) {
      $ascending = 'true';
    } else {
      $ascending = 'false';
    }
    self::$Uri = '/2012-09-25/jobsByPipeline/'.$pipelineId.'?Ascending='.$ascending;
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Get all jobs that have a given status
  *
  * @param string $Status job status
  * @return array | false
  */
  public static function listJobsByStatus($status) {
    self::resetProps();
    self::$HttpRequestMethod = 'GET';
    self::$Uri = '/2012-09-25/jobsByStatus/'.$status;
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Get details of a job
  *
  * @param string $jobId job ID
  * @return array | false
  */
  public static function readJob($jobId) {
    self::resetProps();
    self::$HttpRequestMethod = 'GET';
    self::$Uri = '/2012-09-25/jobs/'.$jobId;
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Cancel a job
  *
  * @param string $jobId job ID
  * @return array | false
  */
  public static function cancelJob($jobId) {
    self::resetProps();
    self::$HttpRequestMethod = 'DELETE';
    self::$Uri = '/2012-09-25/jobs/'.$jobId;
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Create a new pipeline
  *
  * @param array $options settings
  * @return array | false
  */
  public static function createPipeline($options = array()) {
    self::resetProps();
    self::$HttpRequestMethod = 'POST';
    self::$Uri = '/2012-09-25/pipelines';
    $requestBody = array(
      'Name' => $options['Name'],
      'Role' => $options['Role'],
      'InputBucket' => $options['InputBucket'],
      'Notifications' => array(
          'Progressing' => (array_key_exists('Notifications', $options) && array_key_exists('Progressing', $options['Notifications']['Progressing'])) ? $options['Notifications']['Progressing'] : '',
          'Completed' => (array_key_exists('Notifications', $options) && array_key_exists('Completed', $options['Notifications']['Completed'])) ? $options['Notifications']['Completed'] : '',
          'Warning' => (array_key_exists('Notifications', $options) && array_key_exists('Warning', $options['Notifications']['Warning'])) ? $options['Notifications']['Warning'] : '',
          'Error' => (array_key_exists('Notifications', $options) && array_key_exists('Error', $options['Notifications']['Error'])) ? $options['Notifications']['Error'] : ''
        )
      );
    // Either OutputBucket or ContentConfig with ThumbnailConfig are required
    if (array_key_exists('OutputBucket', $options)) {
      $requestBody['OutputBucket'] = $options['OutputBucket'];
    } elseif (array_key_exists('ContentConfig', $options) && array_key_exists('ThumbnailConfig', $options)) {
      $requestBody['ContentConfig'] = $options['ContentConfig'];
      $requestBody['ThumbnailConfig'] = $options['ThumbnailConfig'];
    } else {
      self::setErrorMsg('Missing parameters. OutputBucket or ContentConfig with ThumbnailConfig are required.');
      return false;
    }
    $requestBody = json_encode($requestBody);
    self::setRequestBody($requestBody);
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Get a list of pipelines associated with account
  *
  * @return array | false
  */
  public static function listPipelines() {
    self::resetProps();
    self::$HttpRequestMethod = 'GET';
    self::$Uri = '/2012-09-25/pipelines';
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Get info about a pipeline
  *
  * @param string $pipelineId pipeline ID
  * @return array | false
  */
  public static function readPipeline($pipelineId) {
    self::resetProps();
    self::$HttpRequestMethod = 'GET';
    self::$Uri = '/2012-09-25/pipelines/'.$pipelineId;
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Update settings for a pipeline
  *
  * @param string $pipelineId pipeline ID
  * @param array $updates updates
  * @return array | false
  */
  public static function updatePipeline($pipelineId, $updates) {
    self::resetProps();
    self::$HttpRequestMethod = 'PUT';
    self::$Uri = '/2012-09-25/pipelines/'.$pipelineId;
    $requestBody = json_encode($updates);
    self::setRequestBody($requestBody);
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Update pipeline status (active/paused)
  *
  * @param string $pipelineId pipeline ID
  * @param array $status new status
  * @return array | false
  */
  public static function updatePipelineStatus($pipelineId, $status) {
    self::resetProps();
    self::$HttpRequestMethod = 'POST';
    self::$Uri = '/2012-09-25/pipelines/'.$pipelineId.'/status';
    $requestBody = json_encode(array('Status' => $status));
    self::setRequestBody($requestBody);
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Update pipeline notification settings
  *
  * @param string $pipelineId pipeline ID
  * @param array $notifications new notification settings
  * @return array | false
  */
  public static function updatePipelineNotifications($pipelineId, $notifications = array()) {
    self::resetProps();
    self::$HttpRequestMethod = 'POST';
    self::$Uri = '/2012-09-25/pipelines/'.$pipelineId.'/notifications';
    $requestBody = json_encode(array(
        'Id' => $pipelineId,
        'Notifications' => array(
          'Progressing' => (array_key_exists('Progressing', $notifications)) ? $notifications['Progressing'] : '',
          'Completed' => (array_key_exists('Completed', $notifications)) ? $notifications['Completed'] : '',
          'Warning' => (array_key_exists('Warning', $notifications)) ? $notifications['Warning'] : '',
          'Error' => (array_key_exists('Error', $notifications)) ? $notifications['Error'] : ''
        )
      ));
    self::setRequestBody($requestBody);
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Delete a pipeline
  *
  * @param string $pipelineId pipeline ID
  * @return array | false
  */
  public static function deletePipeline($pipelineId) {
    self::resetProps();
    self::$HttpRequestMethod = 'DELETE';
    self::$Uri = '/2012-09-25/pipelines/'.$pipelineId;
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Test the settings for a pipeline
  *
  * @param string $inputBucket input bucket ID
  * @param string $outBucket output bucket ID
  * @param string $role The IAM Amazon Resource Name (ARN) for role to use for transcoding jobs
  * @param array $topics The ARNs of one or more Amazon Simple Notification Service (Amazon SNS) topics
  * @return array | false
  */
  public static function testRole($inputBucket, $outputBucket, $role, $topics = array()) {
    self::resetProps();
    self::$HttpRequestMethod = 'POST';
    self::$Uri = '/2012-09-25/roleTests';
    $requestBody = json_encode(array(
        'InputBucket' => $inputBucket,
        'OutputBucket' => $outputBucket,
        'Role' => $role,
        'Topics' => $topics
      ));
    self::setRequestBody($requestBody);
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Create a new preset
  *
  * @param string $name name of the preset
  * @param string $description preset description
  * @param string $container container type for output file
  * @param array $audio audio settings
  * @param array $video video settings
  * @param array $thumbnails thumbnail settings
  * @return array | false
  */
  public static function createPreset($name, $description, $container = 'mp4', $audio = array(), $video = array(), $thumbnails = array()) {
    self::resetProps();
    self::$HttpRequestMethod = 'POST';
    self::$Uri = '/2012-09-25/presets';
    $requestBody = array(
        'Name' => $name,
        'Description' => $description,
        'Container' => $container,
        'Audio' => array(
            'Codec' => (array_key_exists('Codec', $audio)) ? $audio['Codec'] : 'AAC',
            'SampleRate' => (array_key_exists('SampleRate', $audio)) ? $audio['SampleRate'] : 'auto',
            'BitRate' => (array_key_exists('BitRate', $audio)) ? $audio['BitRate'] : '64',
            'Channels' => (array_key_exists('Channels', $audio)) ? $audio['Channels'] : 'auto'
          ),
        'Video' => array(
            'Codec' => (array_key_exists('Codec', $video)) ? $video['Codec'] : 'H.264',
            'CodecOptions' => array(
                'Profile' => (array_key_exists('CodecOptions', $video) && array_key_exists('Profile', $video['CodecOptions'])) ? $video['CodecOptions']['Profile'] : 'baseline',
                'Level' => (array_key_exists('CodecOptions', $video) && array_key_exists('Level', $video['CodecOptions'])) ? $video['CodecOptions']['Level'] : '1',
                'MaxReferenceFrames' => (array_key_exists('CodecOptions', $video) && array_key_exists('MaxReferenceFrames', $video['CodecOptions'])) ? $video['CodecOptions']['MaxReferenceFrames'] : '0',
                'MaxBitRate' => (array_key_exists('CodecOptions', $video) && array_key_exists('MaxBitRate', $video['CodecOptions'])) ? $video['CodecOptions']['MaxBitRate'] : '16',
                'BufferSize' => (array_key_exists('CodecOptions', $video) && array_key_exists('BufferSize', $video['CodecOptions'])) ? $video['CodecOptions']['BufferSize'] : '10'
              ),
            'KeyframesMaxDist' => (array_key_exists('KeyframesMaxDist', $video)) ? $video['KeyframesMaxDist'] : '1',
            'FixedGOP' => (array_key_exists('FixedGOP', $video)) ? $video['FixedGOP'] : 'true',
            'BitRate' => (array_key_exists('BitRate', $video)) ? $video['BitRate'] : 'auto',
            'FrameRate' => (array_key_exists('FrameRate', $video)) ? $video['FrameRate'] : 'auto',
            'MaxFrameRate' => (array_key_exists('MaxFrameRate', $video)) ? $video['MaxFrameRate'] : '10',
            'MaxWidth' => (array_key_exists('MaxWidth', $video)) ? $video['MaxWidth'] : 'auto',
            'MaxHeight' => (array_key_exists('MaxHeight', $video)) ? $video['MaxHeight'] : 'auto',
            'SizingPolicy' => (array_key_exists('SizingPolicy', $video)) ? $video['SizingPolicy'] : 'Fit',
            'PaddingPolicy' => (array_key_exists('PaddingPolicy', $video)) ? $video['PaddingPolicy'] : 'Pad',
            'DisplayAspectRatio' => (array_key_exists('DisplayAspectRatio', $video)) ? $video['DisplayAspectRatio'] : 'auto'
          )
      );
    if (isset($video['Resolution']) && isset($video['AspectRatio'])) {
      unset($requestBody['Video']['MaxWidth']);
      unset($requestBody['Video']['MaxHeight']);
      unset($requestBody['Video']['SizingPolicy']);
      unset($requestBody['Video']['PaddingPolicy']);
      unset($requestBody['Video']['DisplayAspectRatio']);
      $requestBody['Video']['Resolution'] = $video['Resolution'];
      $requestBody['Video']['AspectRatio'] = $video['AspectRatio'];
    }
    if (isset($video['Watermarks'])) {
        $requestBody['Video']['Watermarks'] = array(
          'Id' => $video['Watermarks']['Id'],
          'MaxWidth' => (array_key_exists('MaxWidth', $video['Watermarks'])) ? $video['Watermarks']['MaxWidth'] : '16',
          'MaxHeight' => (array_key_exists('MaxHeight', $video['Watermarks'])) ? $video['Watermarks']['MaxHeight'] : '16',
          'SizingPolicy' => (array_key_exists('SizingPolicy', $video['Watermarks'])) ? $video['Watermarks']['SizingPolicy'] : 'Fit',
          'HorizontalAlign' => (array_key_exists('HorizontalAlign', $video['Watermarks'])) ? $video['Watermarks']['HorizontalAlign'] : 'Left',
          'HorizontalOffset' => (array_key_exists('HorizontalOffset', $video['Watermarks'])) ? $video['Watermarks']['HorizontalOffset'] : '0%',
          'VerticalAlign' => (array_key_exists('VerticalAlign', $video['Watermarks'])) ? $video['Watermarks']['VerticalAlign'] : 'Top',
          'VerticalOffset' => (array_key_exists('VerticalOffset', $video['Watermarks'])) ? $video['Watermarks']['VerticalOffset'] : '0%',
          'Opacity' => (array_key_exists('Opacity', $video['Watermarks'])) ? $video['Watermarks']['Opacity'] : '0',
          'Target' => (array_key_exists('Target', $video['Target'])) ? $video['Watermarks']['Target'] : 'Content'
        );
    }
    $requestBody['Thumbnails'] = array(
        'Format' => (array_key_exists('Format', $thumbnails)) ? $thumbnails['Format'] : 'jpg',
        'Interval' => (array_key_exists('Interval', $thumbnails)) ? $thumbnails['Interval'] : '120',
        'MaxWidth' => (array_key_exists('MaxWidth', $thumbnails)) ? $thumbnails['MaxWidth'] : 'auto',
        'MaxHeight' => (array_key_exists('MaxHeight', $thumbnails)) ? $thumbnails['MaxHeight'] : 'auto',
        'SizingPolicy' => (array_key_exists('SizingPolicy', $thumbnails)) ? $thumbnails['SizingPolicy'] : 'Fit',
        'PaddingPolicy' => (array_key_exists('PaddingPolicy', $thumbnails)) ? $thumbnails['PaddingPolicy'] : 'Pad'
      );
    // Resolution and AspectRatio aren't recommended per AWS docs
    if (isset($thumbnails['Resolution']) && isset($thumbnails['AspectRatio'])) {
      unset($requestBody['Thumbnails']['MaxWidth']);
      unset($requestBody['Thumbnails']['MaxHeight']);
      unset($requestBody['Thumbnails']['SizingPolicy']);
      unset($requestBody['Thumbnails']['PaddingPolicy']);
      $requestBody['Thumbnails']['Resolution'] = $thumbnails['Resolution'];
      $requestBody['Thumbnails']['AspectRatio'] = $thumbnails['AspectRatio'];
    }
    $requestBody = json_encode($requestBody);
    self::setRequestBody($requestBody);
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Get a list of all presets
  *
  * @return array | false
  */
  public static function listPresets() {
    self::resetProps();
    self::$HttpRequestMethod = 'GET';
    self::$Uri = '/2012-09-25/presets';
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Get info about a preset
  *
  * @param string $presetId preset ID
  * @return array | false
  */
  public static function readPreset($presetId) {
    self::resetProps();
    self::$HttpRequestMethod = 'GET';
    self::$Uri = '/2012-09-25/presets/'.$presetId;
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Delete a preset
  *
  * @param string $presetId preset ID
  * @return array | false
  */
  public static function deletePreset($presetId) {
    self::resetProps();
    self::$HttpRequestMethod = 'DELETE';
    self::$Uri = '/2012-09-25/presets/'.$presetId;
    $result = self::sendRequest();
    return $result;
  }

  /**
  * Set AWS credentials
  *
  * @param string $awsAccessKey AWS access key
  * @param string $awsPrivateKey AWS private key
  * @return void
  */
  public static function setAuth($awsAccessKey, $awsPrivateKey) {
    self::$AwsAccessKey = $awsAccessKey;
    self::$AwsPrivateKey = $awsPrivateKey;
  }

  /**
  * Set Amazon region
  *
  * @param string $region AWS region
  * @return void
  */
  public static function setRegion($region) {
    self::$Region = strtolower($region);
  }

  /**
  * Get the response HTTP status code
  *
  * @return integer
  */
  public static function getStatusCode() {
    return self::$ResponseStatus;
  }

  /**
  * Get server response
  *
  * @return mixed
  */
  public static function getResponse() {
    return self::$Response;
  }

  /**
  * Get error message after unsuccessful request
  *
  * @return string
  */
  public static function getErrorMsg() {
    return self::$ErrorMsg;
  }

  /**
  * Set error message
  *
  * @param string $error error message
  * @return void
  */
  private static function setErrorMsg($error) {
    self::$ErrorMsg = $error;
  }

  /**
  * Set request body
  *
  * @param mixed $body request body
  * @return void
  */
  private static function setRequestBody($body) {
    self::$RequestBody = $body;
  }

  /**
  * Set request header
  *
  * @param string $key key
  * @param string $value value
  * @return void
  */
  private static function setHeader($key, $value) {
    self::$Headers[$key] = $value;
  }

  /**
  * Reset property values
  *
  * @return void
  */
  private static function resetProps() {
    self::$Headers = array();
    self::$Date = new DateTime('UTC');
    self::$RequestBody = null;
    self::$ResponseStatus = null;
    self::$Response = null;
    self::$ErrorMsg = null;
  }

  /**
  * Executes server request
  *
  * @return array | false
  */
  private static function sendRequest() {
    $endpoint = 'elastictranscoder.'.self::$Region.'.amazonaws.com';
    self::setHeader('Host', $endpoint);
    self::setHeader('x-amz-date', self::$Date->format('Ymd\THis\Z'));
    self::setAuthorizationHeader();

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, self::$HttpRequestMethod);
    $url = 'https://'.$endpoint . self::$Uri;

    if (self::$HttpRequestMethod == 'POST' || self::$HttpRequestMethod == 'PUT') {
      self::setHeader('Content-Type', 'application/json');
      self::setHeader('Content-Length', strlen(self::$RequestBody));
      curl_setopt($curl, CURLOPT_POSTFIELDS, self::$RequestBody);
    }

    $headers = array();
    foreach (self::$Headers as $header => $value) {
      if (strlen($value) > 0)
        $headers[] = $header.': '.$value;
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $result = curl_exec($curl);

    if ($result === false)
      self::setErrorMsg('Curl failed. Error code: '.curl_errno($curl).' Message: '.curl_error($curl));
    else
      self::$ResponseStatus  = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    @curl_close($curl);

    if ($result === false)
      return false;

    $response = json_decode($result, true);
    self::$Response = $response;

    if (in_array(self::$ResponseStatus, array(200, 201, 202)))
      return $response;

    // Apparently "Message" is not always capitalized
    if (isset($response['message']))
      self::setErrorMsg($response['message']);
    if (isset($response['Message']))
      self::setErrorMsg($response['Message']);
    return false;
  }

  private static function hex16($val) {
    $unpack = unpack('H*', $val);
    return reset($unpack);
  }

  private static function parseCanonicalUri($url) {
    $parts = parse_url($url);
    $str = '';
    if (isset($parts['scheme'])) {
      $str .= $parts['scheme'];
    }
    if (isset($parts['host'])) {
      $str .= $parts['host'];
    }
    if (isset($parts['path'])) {
      $str .= $parts['path'];
    }
    return $str;
  }

  private static function parseQueryString($url) {
    $pos = strpos($url, '?');
    if (!$pos)
      return false;
    $url = substr($url, $pos + 1);
    if (empty($url))
      return false;
    $pairs = explode('&', $url);
    $urlVars = array();
    foreach ($pairs as $pair) {
      list($param, $value) = explode('=', $pair, 2);
      $urlVars[$param] = $value;
    }
    return $urlVars;
  }

  /**
  * Builds and sets authorization header
  *
  * @return array | false
  */
  private static function setAuthorizationHeader() {
    $canonicalRequest = array();
    $canonicalRequest[] = self::$HttpRequestMethod;
    $canonicalRequest[] = str_replace('%2F', '/', rawurlencode(self::parseCanonicalUri(self::$Uri)));

    // Format any query string
    $queryParams = self::parseQueryString(self::$Uri);
    if (!$queryParams) {
      $canonicalRequest[] = '';
    } else {
      $qs = '';
      ksort($queryParams);
      foreach($queryParams as $param => $value) {
        $qs .= rawurlencode($param) . '=' . rawurlencode($value) . '&';
      }
      $qs = substr($qs, 0, -1);
      $canonicalRequest[] = $qs;
    }

    $headers = array();
    $canonicalHeaders = array();
    $signedHeaders = array();

    foreach (self::$Headers as $key => $value) {
      $headers[strtolower($key)] = trim($value);
    }

    ksort($headers);

    foreach ($headers as $key => $value) {
      $signedHeaders[] = $key;
      $canonicalHeaders[] = $key . ':' . $value;
    }

    $signedHeaders = implode(';', $signedHeaders);

    $canonicalRequest[] = implode("\n", $canonicalHeaders);
    $canonicalRequest[] = '';
    $canonicalRequest[] = $signedHeaders;
    $canonicalRequest[] = self::hex16(hash('sha256', self::$RequestBody, true));
    $canonicalRequest = implode("\n", $canonicalRequest);

    $stringToSign = array();
    $stringToSign[] = 'AWS4-HMAC-SHA256';
    $stringToSign[] = self::$Date->format('Ymd\THis\Z');

    $credentialScope = array(self::$Date->format('Ymd'));
    $credentialScope[] = self::$Region;
    $credentialScope[] = 'elastictranscoder';
    $credentialScope[] = 'aws4_request';
    $credentialScope = implode('/', $credentialScope);

    $stringToSign[] = $credentialScope;
    $stringToSign[] = self::hex16(hash('sha256', $canonicalRequest, true));
    $stringToSign = implode("\n", $stringToSign);

    $kSecret = 'AWS4'.self::$AwsPrivateKey;
    $kDate = hash_hmac('sha256', self::$Date->format('Ymd'), $kSecret, true);
    $kRegion = hash_hmac('sha256', self::$Region, $kDate, true);
    $kService = hash_hmac('sha256', 'elastictranscoder', $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
    $signature = hash_hmac('sha256', $stringToSign, $kSigning, true);

    $auth = 'AWS4-HMAC-SHA256 Credential='.self::$AwsAccessKey.'/'.$credentialScope;
    $auth .= ',SignedHeaders='.$signedHeaders;
    $auth .= ',Signature='.self::hex16($signature);
    self::setHeader('Authorization', $auth);
  }

}

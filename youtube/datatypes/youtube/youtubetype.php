<?php
/**
 * File containing the youtube class.
 */

/*!
  \class youtube youtube.php
  \ingroup eZDatatype
  \brief The class youtube provides an way to offer YouTube video thumbnails
*/

define ( '_YOUTUBE_ID_', 'YOUTUBE_ID' );
define ( '_YOUTUBE_METADATA_URL_', 'http://gdata.youtube.com/feeds/api/videos/'._YOUTUBE_ID_.'?alt=json' );

class youtube extends eZImageType
{
    const FILESIZE_FIELD = 'data_int1';
    const FILESIZE_VARIABLE = '_ezimage_max_filesize_';
    const DATA_TYPE_STRING = 'youtube';
    const VALID_YOUTUBEURL_REGEX = '/^https?:\/\/(www\.)?youtube\.com\/watch\/?\?v\=[a-z0-9_\-]{6,}$/i';

    private $_thumbnailFilename;

    function youtube()
    {
	$this->_thumbnailFilename = null;
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', 'YouTube', 'Datatype name' ),
                           array( 'translation_allowed' => true, 'serialize_supported' => true ) );
    }

    /**
     * Validate the object attribute input in http. If there is validation failure, there failure message will be put into $contentObjectAttribute->ValidationError
     * @param $http: http object
     * @param $base:
     * @param $contentObjectAttribute: content object attribute being validated
     * @return validation result- eZInputValidator::STATE_INVALID or eZInputValidator::STATE_ACCEPTED
     *
     * @see kernel/classes/eZDataType#validateObjectAttributeHTTPInput($http, $base, $objectAttribute)
     */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $youTubeURL = $http->postVariable( $base . "_data_youtubeurl_" . $contentObjectAttribute->attribute( "id" ) );
	$validYouTubeURL = preg_match( self::VALID_YOUTUBEURL_REGEX, $youTubeURL );
	if ( !$validYouTubeURL ) 
	{
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                     'This is not a valid YouTube URL.' ) );
                return eZInputValidator::STATE_INVALID;
	}
	
	$youTubeId = substr( $youTubeURL, strpos( $youTubeURL, '=' ) + 1 );

	$youTubeMetadataResponse = eZHTTPTool::sendHTTPRequest ( str_replace( _YOUTUBE_ID_, $youTubeId, _YOUTUBE_METADATA_URL_ ), 80, false, 'eZ Publish', false );
	$youTubeMetadataOk = eZHTTPTool::parseHTTPResponse( $youTubeMetadataResponse, $header, $youTubeMetadata );
	if ( $youTubeMetadataOk && ( strpos( $header['content-type'], 'application/json' ) === 0 ) )
	{
	    $firstBrace = strpos( $youTubeMetadata, '{' );
	    $lastBrace = strrpos( $youTubeMetadata, '}' );
            $trimmedMetadata = substr( $youTubeMetadata, $firstBrace, $lastBrace - $firstBrace + 1 );
	    $metadata = json_decode( $trimmedMetadata, true );
	    if ( $metadata === null ) 
	    {
            	$contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                 'Unable to decode the YouTube metadata, check with the site administrator.' ) );
		return eZInputValidator::STATE_INVALID;
	    }
	    $thumbnailURL = $metadata['entry']['media$group']['media$thumbnail'][0]['url'];
	}
	else
	{
            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                 'Unable to get YouTube metadata, check the YouTube URL.' ) );
            return eZInputValidator::STATE_INVALID;
	}

    	$thumbnailURL = 'https://img.youtube.com/vi/'.$youTubeId.'/0.jpg';
        $thumbnailResponse = eZHTTPTool::sendHTTPRequest( $thumbnailURL, 443, false, 'eZ Publish', false );
	$thumbnailOk = eZHTTPTool::parseHTTPResponse( $thumbnailResponse, $header, $thumbnail );

        if ( $thumbnailOk )
        {
             if ( strpos( $header['content-type'], 'image' ) !== 0 )
             {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                     'A valid thumbnail is not available from YouTube.' ) );
                return eZInputValidator::STATE_INVALID;
             }
	}
        if ( $header['content-length'] > ini_get('upload_max_filesize') )
        {
            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                'The size of the thumbnail image exceeds limit set by upload_max_filesize directive in php.ini. Please contact the site administrator.' ) );
            return eZInputValidator::STATE_INVALID;
        }

	$imageManager = new eZImageManager();
	$tmpdir = $imageManager->temporaryImageDirPath();
	$thumbnailFile = tempnam ( $tmpdir, 'ytThumb' );
	file_put_contents ( $thumbnailFile, $thumbnail );
	$this->_thumbnailFilename = $thumbnailFile;
        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * Fetch object attribute http input, override the ezDataType method
     * This method is triggered when submiting a http form which includes 
     * Image is stored into file system every time there is a file input and validation result is valid.
     * @param $http http object
     * @param $base
     * @param $contentObjectAttribute : the content object attribute being handled
     * @return true if content object is not null, false if content object is null
     */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $result = false;
        $content = $contentObjectAttribute->attribute( 'content' );
        if ( $http->hasPostVariable( $base . "_data_youtubeurl_" . $contentObjectAttribute->attribute( "id" ) ) )
        {
            $youTubeURL = $http->postVariable( $base . "_data_youtubeurl_" . $contentObjectAttribute->attribute( "id" ) );
            $content->setAttribute( 'alternative_text', $youTubeURL);
            $content->initializeFromFile( $this->_thumbnailFilename, $youTubeURL );
            $result = true;
        }

        return $result;
    }

    function storeObjectAttribute( $contentObjectAttribute )
    {
        $imageHandler = $contentObjectAttribute->attribute( 'content' );
        if ( $imageHandler )
        {
	    $imageAltText = $imageHandler->attribute( 'alternative_text' );
            $imageHandler->initializeFromFile( $this->_thumbnailFilename, $imageAltText );
            if ( $imageHandler->isStorageRequired() )
            {
                $imageHandler->store( $contentObjectAttribute );
		unlink( $this->_thumbnailFilename );
            }
        }
    }

    /*!
     HTTP file insertion is not supported.
    */
    function isHTTPFileInsertionSupported()
    {
        return false;
    }

    /*!
     Regular file insertion is supported.
    */
    function isRegularFileInsertionSupported()
    {
        return true;
    }
}

eZDataType::register( youtube::DATA_TYPE_STRING, 'youtube' );

?>

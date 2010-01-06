<?php

class Collection
{
  protected $name;
  protected $greenstoneName;
  protected $classifiers;
  protected $collectCfg;
  protected $infodb;
  
  protected function __construct( $name )
  {
    $collection_config = EmeraldviewConfig::get("collections.$name");
    
    if (
      empty( $collection_config )
      || (isset( $collection_config['active'] )
          && $collection_config['active'] === false)
    ) {
      throw new InvalidArgumentException("Not an active collection ($name)");
    }
    
    $this->name = $name;
    
    if (
      !is_readable( $this->getGreenstoneDirectory() )
      || !is_dir( $this->getGreenstoneDirectory() )
    ) {
      $msg = "Trying to load collection $name; could not access Greenstone "
           . 'collection directory (' . $this->getGreenstoneDirectory() .')';
      throw new Exception( $msg );
    }
    
    $this->collectCfg = CollectCfg::factory( $this );
    $this->infodb     = Infodb::factory( $this );
    $this->buildCfg   = BuildCfg::factory( $this );
    // $this->archive    = GreenstoneArchive::factory( $this );
  }
  
  public function getConfig( $subnode = null )
  {
    $node = 'collections.' . $this->name;
    
    if ($subnode) {
      $node .= '.' . $subnode;
    }
    
    return EmeraldviewConfig::get( $node );
  }
  
  public function getIndexes()
  {
    return $this->buildCfg->getIndexes();
  }
  
  public function getCollectCfg()
  {
    return $this->collectCfg;
  }

  public function getBuildCfg()
  {
    return $this->buildCfg;
  }
  
  public function getInfodb()
  {
    return $this->infodb;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getIndexLevels()
  {
    return $this->getCollectCfg()->getLevels();
  }
  
  public function getDefaultIndexLevel()
  {
    return $this->getCollectCfg()->getDefaultLevel();
  }
  
  public function getDisplayName( $language_code = null )
  {
    return $this->getCollectCfg()
           ->getMetadata( 'collectionname', $language_code );
  }
  
  public function getDescription( $language_code = null )
  {
    return $this->getCollectCfg()
           ->getMetadata( 'collectionextra', $language_code );
  }
  
  protected function getGreenstoneName()
  {
    /*
    if ($this->getConfig('gsdl_collect_dir')) {
      return $this->getConfig('gsdl_collect_dir');
    }
    else {
    */
      return $this->name;
    //}
  }
  
  public function getGreenstoneDirectory()
  {
    $dir = EmeraldviewConfig::get('greenstone_collection_dir')
         . '/' . $this->getGreenstoneName();
         
    return $dir;
  }
  
  public function getUrl()
  {
    return url::base() . $this->getName();
  }
  
  public function getClassifier( $classifier_name )
  {
    $classifier_ids = $this->getClassifierIds();
    
    if (in_array( $classifier_name, $classifier_ids )) {
      return new Classifier( $this, $classifier_name );
    }
    else {
      return false;
    }
  }
  
  protected function getClassifierIds()
  {
    return $this->infodb->getClassifierIds();
  }
  
  public function getClassifiers()
  {
    if (isset( $this->classifiers )) {
      return $this->classifiers;
    }
    
    $classifiers = array();
    
    foreach ( $this->getClassifierIds() as $id ) {
      if (
        $this->getConfig( "classifiers.$id.active" ) === false
      ) {
        // this Classifier is not active
        continue;
      }
      
      $classifiers[] = new Classifier( $this, $id );
    }
    
    return $this->classifiers = $classifiers;
  }
  
  public static function factory( $name )
  {
    try {
      return new Collection( $name );
    }
    catch (Exception $e) {
      throw $e;  //FIXME
      Kohana::log('error', $e->getMessage());
      return false;
    }
  }
  
  public static function getAllAvailable()
  {
    $collections_config = EmeraldviewConfig::get('collections');

    $collections = array();
    
    foreach ($collections_config as $name => $config) {
      if ( isset($config['active']) && !$config['active'] ) {
        continue;
      }
      
      $collection = Collection::factory( $name );

      if ($collection) {
        $collections[] = $collection;
      }
    }

    return $collections;
  }
}
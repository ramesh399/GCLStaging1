<?php
namespace app\components;

class JwtValidationData extends \sizeg\jwt\JwtValidationData
{
 
    /**
     * @inheritdoc
     */
    public function init()
    {
        //4f1g23a12aa121255555123TysnQgfRTsdDRRsdf => Demo Key
		//412RsdfLive1GcL2InTeRnationalTerXFGWErwe => Live Key
        $this->validationData->setIssuer('https://ssl.gcl-intl.com');
        $this->validationData->setAudience('https://ssl.gcl-intl.com');
        $this->validationData->setId('412RsdfLive1GcL2InTeRnationalTerXFGWErwe');

        parent::init();
    }
}    
?>
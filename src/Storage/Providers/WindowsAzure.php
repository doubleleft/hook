<?php
namespace Hook\Storage\Providers;

//
// add to your packages.yaml:
// microsoft/windowsazure: dev-master
//

use Hook\Application\Config;
use Exception;

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\CreateBlobOptions;
use WindowsAzure\Blob\Models\PublicAccessType;

class WindowsAzure extends Base
{
    protected $service;

    public function store($filename, $data, $options = array())
    {
        $retrying = isset($options['retry']);
        $options['container'] = Config::get('storage.container', 'default');

        try {
            $this->createBlockBlob($filename, $data, $options);
            return $this->getBlobService()->getUri() . '/' . $options['container'] . '/' . $filename;

        } catch (ServiceException $e) {
            //
            // Blob Container doesn't exists yet. Let's create it.
            //
            if ($e->getCode() == 404 && !$retrying) {
                $create_container_options = new CreateContainerOptions();
                $create_container_options->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
                $create_container_options->addMetaData('created', date('U'));
                $this->getBlobService()->createContainer($options['container'], $create_container_options);

                // Container has been created. Let's retry storing blob file.
                $options['retry'] = true;
                return $this->store($filename, $data, $options);
            } else {
                throw $e;
            }
        }

    }

    protected function createBlockBlob($filename, $data, $options) {
        $create_blob_options = new CreateBlobOptions();

        if (isset($options['mime'])) {
            $create_blob_options->setBlobContentType($options['mime']);
        }

        return $this->getBlobService()->createBlockBlob($options['container'], $filename, $data, $create_blob_options);
    }

    protected function getBlobService() {
        if (!$this->service) {
            $endpoint = Config::get('storage.endpoint_protocol', 'https');
            if (!$endpoint) { throw new Exception(__CLASS__ . ": 'storage.endpoint_protocol' config is required."); }

            $account = Config::get('storage.account');
            if (!$account) { throw new Exception(__CLASS__ . ": 'storage.account' config is required."); }

            $key = Config::get('storage.key');
            if (!$key) { throw new Exception(__CLASS__ . ": 'storage.key' config is required."); }

            $conection = array(
                "DefaultEndpointsProtocol={$endpoint}",
                "AccountName={$account}",
                "AccountKey={$key}"
            );
            $this->service = ServicesBuilder::getInstance()->createBlobService(join(";", $conection));
        }

        return $this->service;
    }

}

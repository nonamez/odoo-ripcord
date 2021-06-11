<?php

namespace Ripoo\Handler;

use Ripoo\Service\ModelService;
use Ripoo\Exception\{RipooException, AuthException, ResponseException, ResponseFaultException, ResponseStatusException};

/**
 * Handle related to Odoo Model/Object Service/Endpoint
 * @see https://www.odoo.com/documentation/11.0/reference/orm.html#reference-orm-model
 * @author Thomas Bondois
 */
trait ModelHandlerTrait
{
    /**
     * "Object" Endpoint, "Model" service
     * odoo.service.model.dispatch
     *
     * @return ModelService
     */
    public function getModelService() : ModelService
    {
        return $this->getService(ModelService::ENDPOINT);
    }

    /**
     * @param string $model
     * @param string $method
     * @param array|null $args argument list, ordered. sequential-array (Python-List) containing, for each numeric index, scalar or array
     * @param array|null $kwargs extra argument list, named. associative-array  (Python-Dictionary) containing, for each keyword, scalar or array
     * @return mixed
     *
     * @author Thomas Bondois
     */
    public function model_execute_kw(string $model, string $method, $args = null, $kwargs = null)
    {
        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            $method,
            $args,
            $kwargs
        );
        return $this->setResponse($response);
    }

    /**
     * @param string $model
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     * @author Thomas Bondois <thomas.bondois@agence-tbd.com>
     */
    public function model_execute_splat(string $model, string $method, ...$args)
    {
        dd(__FUNCTION__);
        $response = $this->getModelService()->execute(
            $this->db, $this->uid(), $this->password,
            $model,
            $method,
            $args
        );
        return $this->setResponse($response);
    }

    /**
     * @see https://odoo-restapi.readthedocs.io/en/latest/calling_methods/check_access_rights.html
     *
     * @param string $model
     * @param string $permission see OPERATION_* constants
     * @param bool $withExceptions
     *
     * @return bool
     * @throws AuthException|ResponseException
     *
     * @author Thomas Bondois
     */
    public function check_access_rights(string $model, string $permission = self::OPERATION_READ, bool $withExceptions = false)
    {
        if (!is_array($permission)) {
            $permission = [$permission];
        }
        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            'check_access_rights',
            $permission,
            ['raise_exception' => $withExceptions]
        );

        //TODO analyse result fault etc
        return (bool)$this->setResponse($response);
    }

    /**
     * Search models
     *
     * @param string $model Model
     * @param array $criteria Array of criteria
     * @param integer $offset Offset
     * @param integer $limit Max results
     * @param string $order
     *
     * @return array Array of model id's
     * @throws AuthException|ResponseException
     */
    public function search(string $model, array $criteria = [], $offset = 0, $limit = 0, $order = '')
    {
        $kwargs = [
            'offset' => $offset,
            'limit'  => $limit,
            'order'  => $order
        ];

        if ($this->currentLang) {
            $kwargs['context'] = [
                'lang' => $this->currentLang
            ];
        }

        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            'search',
            [$criteria],

            $kwargs
        );
        return $this->setResponse($response);
    }

    /**
     * Search_count models
     *
     * @param string $model Model
     * @param array $criteria Array of criteria
     *
     * @return int
     * @throws AuthException|ResponseException
     */
    public function search_count(string $model, array $criteria = [])
    {
        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            'search_count',
            [$criteria]
        );
        return $this->setResponse($response);
    }

    /**
     * Read model(s)
     *
     * @param string $model Model
     * @param array $ids Array of model (external) id's
     * @param array $fields Index array of fields to fetch, an empty array fetches all fields
     *
     * @return array An array of models
     * @throws AuthException|ResponseException
     */
    public function read(string $model, array $ids, array $fields = [])
    {
        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            'read',
            [$ids],
            ['fields' => $fields]
        );
        return $this->setResponse($response);
    }

    /**
     * Search and Read model(s)
     *
     * @param string $model Model
     * @param array $criteria Array of criteria
     * @param array $fields Index array of fields to fetch, an empty array fetches all fields
     * @param integer $limit Max results
     * @param string $order
     *
     * @return array An array of models
     * @throws AuthException|ResponseException
     */
    public function search_read(string $model, array $criteria, array $fields = [], int $limit = 0, $order = '')
    {
        $kwargs = [
            'fields' => $fields,
            'limit'  => $limit,
            'order'  => $order,
        ];

        if ($this->currentLang) {
            $kwargs['context'] = [
                'lang' => $this->currentLang
            ];
        }

        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            'search_read',
            [$criteria],
            $kwargs
            
        );
        return $this->setResponse($response);
    }

    /**
     * @see https://www.odoo.com/documentation/11.0/reference/orm.html#odoo.models.Model.fields_get
     *
     * @param string $model
     * @param array $fields
     * @param array $attributes
     *
     * @return mixed
     * @throws AuthException|ResponseException
     *
     * @author Thomas Bondois
     */
    public function fields_get(string $model, array $fields = [], array $attributes = [])
    {
        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            'fields_get',
            $fields,
            ['attributes' => $attributes]
        );
        return $this->setResponse($response);
    }

    /**
     * Create model
     *
     * @param string $model Model
     * @param array $data Array of fields with data (format: ['field' => 'value'])
     *
     * @return int Created model id
     * @throws AuthException|ResponseException
     */
    public function create(string $model, array $data)
    {
        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            'create',
            [$data]
        );
        return $this->setResponse($response);
    }

    /**
     * Update model(s)
     *
     * @param string $model Model
     * @param array $ids Model ids to update
     * @param array $fields A associative array (format: ['field' => 'value'])
     *
     * @return array
     * @throws AuthException
     * @throws ResponseFaultException|ResponseStatusException
     */
    public function write(string $model, array $ids, array $fields)
    {
        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            'write',
            [   $ids,
                $fields,
            ]
        );
        return $this->setResponse($response);
    }

    /**
     * Unlink model(s)
     *
     * @param string $model Model
     * @param array $ids Array of model id's
     *
     * @return boolean successful or not
     * @throws AuthException|ResponseException
     */
    public function unlink(string $model, array $ids)
    {
        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            $model,
            'unlink',
            [$ids]
        );
        return $this->setResponse($response);
    }

    public function translate_field(string $model, int $template_id, string $field)
    {
        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            'ir.translation',
            'translate_fields',
            [$model, $template_id, $field]
        );

        return $this->setResponse($response);
    }

    public function action_archive(int $template_id, int $company_id) {
        $kwargs = [
            'context' => [
                'allowed_company_ids' => [$company_id]
            ]
        ];

        $response = $this->getModelService()->execute_kw(
            $this->db, $this->uid(), $this->password,
            'product.template',
            'action_archive',
            [[$template_id]],
            $kwargs
        );

        return $this->setResponse($response);
    }
}

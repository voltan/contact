<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Contact\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Contact\Form\DepartmentForm;
use Module\Contact\Form\DepartmentFilter;

class DepartmentController extends ActionController
{
    protected $departmentColumns = array(
        'id', 'title', 'slug', 'email', 'status'
    );

    public function indexAction()
    {
        // Set template
        $this->view()->setTemplate('department_index');
        // Get info
        $select = $this->getModel('department')->select()->order(array('id DESC'));
        $rowset = $this->getModel('department')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
        }
        // Set view
        $this->view()->assign('departments', $list);

    }

    public function updateAction()
    {
        $id = $this->params('id');
        $form = new DepartmentForm('department');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            // Set slug
            $slug = ($data['slug']) ? $data['slug'] : $data['title'];
            $slug = _strip($slug);
            $slug = strtolower(trim($slug));
            $slug = array_filter(explode(' ', $slug));
            $slug = implode('-', $slug);
            $data['slug'] = $slug;
            // Form filter
            $form->setInputFilter(new DepartmentFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->departmentColumns)) {
                        unset($values[$key]);
                    }
                }
                // Save values
                if (!empty($values['id'])) {
                    $row = $this->getModel('department')->find($values['id']);
                } else {
                    $row = $this->getModel('department')->createRow();
                }
                $row->assign($values);
                $row->save();
                // Check it save or not
                if ($row->id) {
                    $message = __('Department data saved successfully.');
                    $this->jump(array('action' => 'index'), $message);
                }
            }
        } else {
            if ($id) {
                $values = $this->getModel('department')->find($id)->toArray();
                $form->setData($values);
            }
        }
        $this->view()->setTemplate('department_update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a Department'));
    }

    public function deleteAction()
    {
        // Get information
        $this->view()->setTemplate(false);
        $id = $this->params('id');
        $row = $this->getModel('department')->find($id);
        if ($row) {
            // Remove message
            $this->getModel('message')->delete(array('department' => $row->id));
            // Remove page
            $row->delete();
            $this->jump(array('action' => 'index'), __('Your selected department deleted'));
        }
        $this->jump(array('action' => 'index'), __('Please select department'));
    }
}
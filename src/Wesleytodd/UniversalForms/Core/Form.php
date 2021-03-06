<?php

namespace Wesleytodd\UniversalForms\Core;

use stdClass;
use IteratorAggregate;
use ArrayIterator;
use Serializable;
use JsonSerializable;

class Form implements IteratorAggregate, JsonSerializable, Serializable {

	/**
	 * Form attributes
	 *
	 * @var array
	 * @access public
	 */
	public $attributes = array();

	/**
	 * Form fields
	 *
	 * @var array
	 * @access public
	 */
	public $fields = array();

	/**
	 * Constructor
	 *
	 * @param string|array $form an array or json string UniversalForm declaration
	 * @param array $opts an array of other options
	 */
	public function __construct($form = null, $input = null) {
		if ($form !== null) {
			if (!is_string($form)) {
				$form = json_encode($form);
			}
			$this->unserialize($form);
		}

		if ($input !== null) {
			$this->populate($input);
		}
	}

	/**
	 * Add a field to the form
	 *
	 * @param string|Field $name the field name or an instance of a field
	 * @param string $type the field type
	 * @param string|array $field the field declaration
	 * @param arrat $opts an array of options
	 */
	public function addField($name, $type = '', $field = array()) {
		if ($name instanceof Field) {
			$this->fields[$name->name] = $name;
			return $this;
		}

		$this->fields[$name] = new Field($name, $type, $field);
		return $this;
	}

	/**
	 * Remove a field from the form
	 *
	 * @param string $name the field name
	 */
	public function removeField($name) {
		unset($this->fields[$name]);
		return $this;
	}

	/**
	 * Populate the values
	 *
	 * @param array $input
	 */
	public function populate($input) {
		foreach ($this as $field) {
			if (isset($input[$field->name])) {
				$field->value = $input[$field->name];
			} else {
				$field->value = '';
			}
		}
		return $this;
	}

	public function getValues() {
		$values = array();
		foreach ($this as $field) {
			$values[$field->name] = $field->value;
		}
		return $values;
	}

	public function getRules() {
		$rules = array();
		foreach ($this as $field) {
			$rules[$field->name] = $field->getRules();
		}
		return $rules;
	}

	/**
	 * Implementing iterator interface
	 */
	public function getIterator() {
		return new ArrayIterator($this->fields);
	}

	/**
	 * Implementing serializeable serialize
	 */
	public function serialize($asJSON = true) {
		if ($asJSON) {
			return json_encode($this);
		} else {
			return $this->jsonSerialize();
		}
	}

	/**
	 * Implementing serializeable unserialize
	 */
	public function unserialize($data) {
		$form = json_decode($data);
		if (!isset($form->attributes)) {
			$form->attributes = array();
		}
		$form->attributes['id']     = (isset($form->id)) ? $form->id : '';
		$form->attributes['method'] = (isset($form->method)) ? $form->method : 'POST';
		$form->attributes['action'] = (isset($form->action)) ? $form->action : '';

		$this->attributes = $form->attributes;

		foreach ($form->fields as $field) {
			$this->addField($field->name, $field->type, $field);
		}
	}

	/**
	 * Implementing jsonSerialize
	 */
	public function jsonSerialize() {
		$fields = array();
		foreach ($this as $field) {
			$fields[] = $field->serialize(false);
		}
		return array(
			'id' => $this->attributes['id'],
			'method' => $this->attributes['method'],
			'action' => $this->attributes['action'],
			'fields' => $fields
		);
	}

	public function __toString(){
		return $this->serialize();
	}

}

<?php
namespace PostFinanceCheckout\Entity;

/**
 * Defines the different resource types
 */
interface ResourceType {
	const STRING = 'string';
	const DATETIME = 'datetime';
	const INTEGER = 'integer';
	const BOOLEAN = 'boolean';
	const OBJECT = 'object';
	const DECIMAL = 'decimal';
}
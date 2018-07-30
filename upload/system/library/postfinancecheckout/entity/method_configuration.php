<?php

namespace PostFinanceCheckout\Entity;

/**
 * This entity holds data about a PostFinanceCheckout payment method.
 *
 * @method int getId()
 * @method string getState()
 * @method void setState(string $state)
 * @method int getSpaceId()
 * @method void setSpaceId(int $id)
 * @method int getConfigurationId()
 * @method void setConfigurationId(int $id)
 * @method string getConfigurationName()
 * @method void setConfigurationName(string $name)
 * @method string[] getTitle()
 * @method void setTitle(string[] $title)
 * @method string[] getDescription()
 * @method void setDescription(string[] $description)
 * @method string getImage()
 * @method void setImage(string $image)
 * @method int getSortOrder()
 * @method void setSortOrder(int $sortOrder)
 *
 */
class MethodConfiguration extends AbstractEntity {
	const STATE_ACTIVE = 'active';
	const STATE_INACTIVE = 'inactive';
	const STATE_HIDDEN = 'hidden';

	protected static function getFieldDefinition(){
		return array(
			'state' => ResourceType::STRING,
			'space_id' => ResourceType::INTEGER,
			'configuration_id' => ResourceType::INTEGER,
			'configuration_name' => ResourceType::STRING,
			'sort_order' => ResourceType::INTEGER,
			'title' => ResourceType::OBJECT,
			'description' => ResourceType::OBJECT,
			'image' => ResourceType::STRING 
		);
	}

	protected static function getTableName(){
		return 'postfinancecheckout_method_configuration';
	}

	public static function loadByConfiguration(\Registry $registry, $space_id, $configuration_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . self::getTableName();
		$space_id = $db->escape($space_id);
		$configuration_id = $db->escape($configuration_id);
		$query = "SELECT * FROM $table WHERE space_id='$space_id' AND configuration_id='$configuration_id'";
		
		$result = $db->query($query);
		
		if (isset($result->row) && !empty($result->row)) {
			return new self($registry, $result->row);
		}
		return new self($registry);
	}

	public static function loadBySpaceId(\Registry $registry, $space_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . self::getTableName();
		$space_id = $db->escape($space_id);
		$query = "SELECT * FROM $table WHERE space_id='$space_id'";
		
		$db_result = $db->query($query);
		
		$result = array();
		if ($db_result->num_rows) {
			foreach ($db_result->rows as $row) {
				$result[] = new static($registry, $row);
			}
		}
		return $result;
	}
}
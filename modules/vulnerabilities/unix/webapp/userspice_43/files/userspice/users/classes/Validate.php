<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class Validate
{
	public
			$_errors = [],
			$_db     = null;

	public function __construct()  {
		$this->_db = DB::getInstance();
	}

	public function check($source, $items=[], $sanitize=true) {
		$this->_errors = [];

		foreach ($items as $item => $rules) {
			$item    = sanitize($item);
			$display = $rules['display'];

			foreach ($rules as $rule => $rule_value) {
				$value = $source[$item];

				if ($sanitize)
					$value = sanitize(trim($value));

				$length = is_array($value) ? count($value) : strlen($value);
				$verb   = is_array($value) ? "are"         : "is";

				if ($rule==='required'  &&  $length==0) {
					if ($rule_value)
						$this->addError(["{$display} {$verb} required",$item]);
				}
				else
				if ($length != 0) {
					switch ($rule) {
						case 'min':
							if (is_array($rule_value))
								$rule_value = max($rule_value);

							if ($length < $rule_value)
								$this->addError(["{$display} must be a minimum of {$rule_value} characters",$item]);
							break;

						case 'max':
							if (is_array($rule_value))
								$rule_value = min($rule_value);

							if ($length > $rule_value)
								$this->addError(["{$display} must be a maximum of {$rule_value} characters",$item]);
							break;

						case 'matches':
							if (!is_array($rule_value))
								$array = [$rule_value];

							foreach ($array as $rule_value)
								if ($value != $source[$rule_value])
									$this->addError(["{$items[$rule_value]['display']} and {$display} must match",$item]);
							break;

						case 'unique':
							$table  = is_array($rule_value) ? $rule_value[0] : $rule_value;
							$fields = is_array($rule_value) ? $rule_value[1] : [$item, '=', $value];

							if ($this->_db->get($table, $fields)) {
								if ($this->_db->count())
									$this->addError(["{$display} already exists. Please choose another {$display}",$item]);
							} else
								$this->addError(["Cannot verify {$display}. Database error",$item]);
							break;

						case 'unique_update':
							$t     = explode(',', $rule_value);
							$table = $t[0];
							$id    = $t[1];
							$query = "SELECT * FROM {$table} WHERE id != {$id} AND {$item} = '{$value}'";
							$check = $this->_db->query($query);

							if ($check->count())
								$this->addError(["{$display} already exists. Please choose another {$display}",$item]);
							break;

						case 'is_numeric': case 'is_num':
							if ($rule_value  &&  !is_numeric($value))
								$this->addError(["{$display} has to be a number. Please use a numeric value",$item]);
							break;

						case 'valid_email':
							if(!filter_var($value,FILTER_VALIDATE_EMAIL))
								$this->addError(["{$display} must be a valid email address",$item]);
							break;

						case '<'  :
						case '>'  :
						case '<=' :
						case '>=' :
						case '!=' :
						case '==' :
							$array = is_array($rule_value) ? $rule_value : [$rule_value];

							foreach ($array as $rule_value)
								if (is_numeric($value)) {
									$rule_value_display = $rule_value;

									if (!is_numeric($rule_value)  &&  isset($source[$rule_value])) {
										$rule_value_display = $items[$rule_value]["display"];
										$rule_value         = $source[$rule_value];
									}

									if ($rule=="<"  &&  $value>=$rule_value)
										$this->addError(["{$display} must be smaller than {$rule_value_display}",$item]);

									if ($rule==">"  &&  $value<=$rule_value)
										$this->addError(["{$display} must be larger than {$rule_value_display}",$item]);

									if ($rule=="<="  &&  $value>$rule_value)
										$this->addError(["{$display} must be equal {$rule_value_display} or smaller",$item]);

									if ($rule==">="  &&  $value<$rule_value)
										$this->addError(["{$display} must be equal {$rule_value_display} or larger",$item]);

									if ($rule=="!="  &&  $value==$rule_value)
										$this->addError(["{$display} must be different from {$rule_value_display}",$item]);

									if ($rule=="=="  &&  $value!=$rule_value)
										$this->addError(["{$display} must equal {$rule_value_display}",$item]);
								}
								else
									$this->addError(["{$display} has to be a number. Please use a numeric value",$item]);
							break;

						case 'is_integer': case 'is_int':
							if ($rule_value  &&  filter_var($value, FILTER_VALIDATE_INT)===false)
								$this->addError(["{$display} has to be an integer",$item]);
							break;

						case 'is_timezone':
							if ($rule_value)
								if (array_search($value, DateTimeZone::listIdentifiers(DateTimeZone::ALL)) === FALSE)
									$this->addError(["{$display} has to be a valid time zone name",$item]);
						break;

						case 'in':
							$verb           = "have to be";
							$list_of_names  = [];	// if doesn't match then display these in an error message
							$list_of_values = [];	// to compare it against

							if (!is_array($rule_value))
								$rule_value = [$rule_value];

							foreach($rule_value as $val)
								if (!is_array($val)) {
									$list_of_names[]  = $val;
									$list_of_values[] = strtolower($val);
								} else
									if (count($val) > 0) {
										$list_of_names[]  = $val[0];
										$list_of_values[] = strtolower((count($val)>1 ? $val[1] : $val[0]));
									}

							if (!is_array($value)) {
								$verb  = "has to be one of the following";
								$value = [$value];
							}

							foreach ($value as $val) {
								if (array_search(strtolower($val), $list_of_values) === FALSE) {
									$this->addError(["{$display} {$verb}: ".implode(', ',$list_of_names),$item]);
									break;
								}
							}
						break;

						case 'is_datetime':
						if ($rule_value !== false) {
							$object = DateTime::createFromFormat((empty($rule_value) || is_bool($rule_value) ? "Y-m-d H:i:s" : $rule_value), $value);

							if (!$object  ||  DateTime::getLastErrors()["warning_count"]>0  ||  DateTime::getLastErrors()["error_count"]>0)
								$this->addError(["{$display} has to be a valid time",$item]);
						}
						break;
					}
				}
			}

		}

		return $this;
	}

	public function addError($error) {
		if (array_search($error, $this->_errors) === FALSE)
			$this->_errors[] = $error;
	}

	public function display_errors() {
		$html = "<UL CLASS='bg-danger'>";

		foreach($this->_errors as $error) {
			if (is_array($error))
				$html    .= "<LI CLASS='text-danger'>{$error[0]}</LI>
						     <SCRIPT>jQuery('document').ready(function(){jQuery('#{$error[1]}').parent().closest('div').addClass('has-error');});</SCRIPT>";
			else
				$html .= "<LI CLASS='text-danger'>{$error}</LI>";
		}

		$html .= "</UL>";
		return $html;
	}

	public function errors(){
		return $this->_errors;
	}

	public function passed(){
		return empty($this->_errors);
	}
}

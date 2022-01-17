<?php
/*
** Zabbix
** Copyright (C) 2001-2021 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * Top hosts data widget form.
 */
class CWidgetFormTopHostsData extends CWidgetForm {

	const ORDER_NONE = 1;
	const ORDER_TOPN = 2;
	const ORDER_BOTTOMN = 3;

	const DEFAULT_HOSTS_COUNT = 10;

	public function __construct($data, $templateid) {
		parent::__construct($data, $templateid, WIDGET_TOP_HOSTS_DATA);

		$this->data = self::convertDottedKeys($this->data);

		// Apply sortable changes to data.
		if (array_key_exists('sortorder', $this->data)) {
			foreach ($this->data['sortorder'] as $key => $sortorder) {
				if (!array_key_exists($key, $this->data)) {
					continue;
				}

				$sorted = [];

				foreach ($sortorder as $index) {
					$sorted[] = $this->data[$key][$index];
				}

				$this->data[$key] = $sorted;
			}
		}

		// Host groups.
		$field_groups = new CWidgetFieldMsGroup('groupids', _('Host groups'));

		if (array_key_exists('groupids', $this->data)) {
			$field_groups->setValue($this->data['groupids']);
		}

		$this->fields[$field_groups->getName()] = $field_groups;

		// Hosts.
		$field_hosts = new CWidgetFieldMsHost('hostids', _('Hosts'));
		$field_hosts->filter_preselect_host_group_field = 'groupids_';

		if (array_key_exists('hostids', $this->data)) {
			$field_hosts->setValue($this->data['hostids']);
		}

		$this->fields[$field_hosts->getName()] = $field_hosts;

		// Tag evaltype (And/Or).
		$field_evaltype = (new CWidgetFieldRadioButtonList('evaltype', _('Host tags'), [
			TAG_EVAL_TYPE_AND_OR => _('And/Or'),
			TAG_EVAL_TYPE_OR => _('Or')
		]))
			->setDefault(TAG_EVAL_TYPE_AND_OR)
			->setModern(true);

		if (array_key_exists('evaltype', $this->data)) {
			$field_evaltype->setValue($this->data['evaltype']);
		}

		$this->fields[$field_evaltype->getName()] = $field_evaltype;

		// Tags array: tag, operator and value. No label, because it belongs to previous group.
		$field_tags = new CWidgetFieldTags('host_tags', '');

		if (array_key_exists('host_tags', $this->data)) {
			$field_tags->setValue($this->data['host_tags']);
		}

		$this->fields[$field_tags->getName()] = $field_tags;

		// Columns definition table.
		$field_columns = (new CWidgetFieldColumnsList('columns', _('Columns')))
			->setFlags(CWidgetField::FLAG_LABEL_ASTERISK)
			->setValue(array_key_exists('columns', $this->data) ? $this->data['columns'] : []);
		$this->fields[$field_columns->getName()] = $field_columns;

		// Order.
		$field_order = (new CWidgetFieldRadioButtonList('order', _('Order'), [
			self::ORDER_NONE => _('None'),
			self::ORDER_TOPN => _('Top N'),
			self::ORDER_BOTTOMN => _('Bottom N')
		]))
			->setDefault(self::ORDER_NONE)
			->setModern(true);

		if (array_key_exists('order', $this->data)) {
			$field_order->setValue($this->data['order']);
		}

		$this->fields[$field_order->getName()] = $field_order;

		// Field column.
		$values = [];

		foreach ($field_columns->getValue() as $key => $value) {
			if ($value['data'] == CWidgetFieldColumnsList::DATA_ITEM_VALUE) {
				$values[$key] = $value['item'];
			}
		}

		$field_column = (new CWidgetFieldSelect('column', _('Column'), $values))->setFlags(
			($field_order->getValue() == self::ORDER_NONE)
				? CWidgetField::FLAG_DISABLED
				: CWidgetField::FLAG_LABEL_ASTERISK
		);

		if (array_key_exists('column', $this->data)) {
			// Fix selected column index if columns were sorted.
			if (array_key_exists('sortorder', $this->data) && array_key_exists('columns', $this->data['sortorder'])) {
				$this->data['column'] = array_search($this->data['column'], $this->data['sortorder']['columns']);
			}

			$field_column->setValue($this->data['column']);
		}
		else {
			reset($values);
			$field_column->setValue((int) key($values));
		}

		$this->fields[$field_column->getName()] = $field_column;

		// Hosts count.
		$field_hosts_count = (new CWidgetFieldIntegerBox('hosts_count', _('Hosts count'), ZBX_MIN_WIDGET_LINES,
			ZBX_MAX_WIDGET_LINES
		))
			->setFlags(CWidgetField::FLAG_LABEL_ASTERISK)
			->setDefault(self::DEFAULT_HOSTS_COUNT);

		if (array_key_exists('hosts_count', $this->data)) {
			$field_hosts_count->setValue((int) $this->data['hosts_count']);
		}

		$this->fields[$field_hosts_count->getName()] = $field_hosts_count;
	}
}

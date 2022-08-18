import React from 'react';
import { Button, Form, Input, Select, DatePicker } from 'antd';
import { connect } from 'dva';
import classNames from 'classnames';
import moment from 'moment';
import {debounce} from 'lodash';
const { Search } = Input;

const Filter = ({ moduleData, dispatch, form, changeFilterForm }) => {

  const handleFilterButton = addParams => () => {
    dispatch({
      type: 'skGlobal/handleFilterButton',
      payload: {
        path: moduleData.get('path'),
        addParams,
      },
    });
  };

  const filters = moduleData.getIn(['params', 'barElements']);

  const { getFieldDecorator } = form;

  const filterFields = filters.toJS().map(filter => {
    let field = {};

    switch (filter.libName) {
      case 'Ext.Builder.ListFilterText':
        field = (
          <Form.Item>
            {getFieldDecorator(filter.fieldName, {
              initialValue: filter.fieldValue,
            })(
              <Search
                allowClear
                placeholder={filter.emptyText}
                onSearch={debounce(changeFilterForm(form), 50)}
              />
            )}
          </Form.Item>
        );

        break;

      case 'Ext.Builder.ListFilterSelect':
        const checkedIndex = filter.menu.items.findIndex(value => {
          return !!value.checked;
        });

        const checkedValue = checkedIndex !== -1 ? filter.menu.items[checkedIndex].data : false;
        const options = filter.menu.items.map(value => {
          return (
            <Select.Option key={value.data} value={value.data}>
              <div dangerouslySetInnerHTML={{ __html: value.text }} />
            </Select.Option>
          );
        });

        field = (
          <Form.Item label={filter.text}>
            {getFieldDecorator(filter.fieldName, {
              initialValue: checkedValue,
            })(
              <Select
                dropdownMatchSelectWidth={false}
                onChange={debounce(changeFilterForm(form), 50)}
              >
                {options}
              </Select>
            )}
          </Form.Item>
        );

        break;

      case 'Ext.Builder.ListFilterDate':
        const dateFormat = 'YYYY/MM/DD';

        const begin = filter.fieldValue[0] ? moment(filter.fieldValue[0], dateFormat) : '';
        const end = filter.fieldValue[1] ? moment(filter.fieldValue[1], dateFormat) : '';

        field = (
          <Form.Item>
            {getFieldDecorator(filter.fieldName, {
              initialValue: [begin, end],
            })(<DatePicker.RangePicker />)}
          </Form.Item>
        );
        break;

      case 'Ext.Builder.ListFilterButton':
        field = (
          <Form.Item>
            <Button
              onClick={handleFilterButton(filter.addParams)}
            >
              {filter.text}
            </Button>
          </Form.Item>
        );
        break;

      default:
        break;
    }

    return field;
  });

  return (
    <Form
      className={classNames('sk-cat-filter')}
      layout="inline"
    >
      {filterFields.map(value => value)}
    </Form>
  )
};

const FormFilter = Form.create()(Filter);
export default connect(({ skGlobal }, { tabKey }) => {
  return {
    moduleData: skGlobal.tabs.get(tabKey),
  };
})(FormFilter);

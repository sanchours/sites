import React from 'react';
import { Form, DatePicker } from 'antd';
import moment from 'moment';
import styles from './style.less'

export default ({form, formClassName, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, disabled} = configField;
  const displayFormat = 'DD.MM.YYYY';
  const serverFormat = 'YYYY-MM-DD';

  const getHandledValue = (value) => {
    return (value instanceof moment) ? value.format(serverFormat) : value;
  };

  return (
    <Form.Item
      key={name}
      help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
      label={title}
      className={styles.DateField}
    >
      {
        getFieldDecorator(name, {
          getValueProps: (value) => {
            return {
              value: (!value || (value === '0000-00-00')) ? moment() : moment(value, serverFormat)
            };
          },
          handledValue: getHandledValue,
          rules: [{ required: false, message: '' }],
        })(
            <DatePicker
              getCalendarContainer={() => document.getElementsByClassName(formClassName)[0]}
              disabled={!!disabled}
              format={displayFormat}
              allowClear={false}
            />
        )
      }
    </Form.Item>
  )
}

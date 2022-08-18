import React from 'react';
import { Form, TimePicker } from 'antd';
import moment from 'moment';
import styles from './style.less'

export default ({form, formClassName, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, disabled} = configField;
  const serverFormat = 'HH:mm:ss';
  const displayFormat = 'HH:mm';

  const getHandledValue = (value) => {
    return (value instanceof moment) ? value.format(serverFormat) : value;
  };

  return (
    <Form.Item
      key={name}
      help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
      label={title}
    >
      {
        getFieldDecorator(name, {
          getValueProps: (value) => {
            return {
              value: (!value || (value === '00:00:00')) ? moment() : moment(value, serverFormat)
            };
          },
          handledValue: getHandledValue,
          rules: [{ required: false, message: '' }],
        })(
          <TimePicker
            getPopupContainer={() => document.getElementsByClassName(formClassName)[0]}
            disabled={!!disabled}
            format={displayFormat}
            allowClear={false}
          />
        )
      }
    </Form.Item>
  );
}

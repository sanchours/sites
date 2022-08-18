import React from 'react';
import { Form, InputNumber } from 'antd';
import styles from './style.less'

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, minValue, maxValue, value, title, subtext, disabled} = configField;
  const config = {};

  if (disabled !== undefined) {
    config.disabled = !!disabled;
  }

  if (minValue !== undefined) {
    config.min = minValue;
  }

  if (maxValue !== undefined) {
    config.max = maxValue;
  }

  // if ( allowDecimals !== undefined ){
  //     config.step = 0.1;
  // }
  return (
    <Form.Item
      key={name}
      help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
      label={title}
    >
      {
        getFieldDecorator(name, {
          initialValue: value,
          rules: [{ required: false, message: 'asdasd' }],
        })(<InputNumber {...config} disabled={!!disabled} />)
      }
    </Form.Item>
  )
}

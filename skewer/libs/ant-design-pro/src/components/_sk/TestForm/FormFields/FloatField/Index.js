import React from 'react';
import { Form, InputNumber } from 'antd';

export default ({form, moduleData, configField, dispatch}) => {

  const {getFieldDecorator} = form;
  const {name, minValue, maxValue, step, value, title, subtext, disabled} = configField;
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

  if (step !== undefined) {
    config.step = step;
  }

  return (
    <Form.Item
      key={name}
      help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
      label={title}
    >
      {
        getFieldDecorator(name, {
          initialValue: value,
          rules: [{ required: false, message: '' }],
        })(
          <InputNumber {...config} />
        )
      }
    </Form.Item>
  )
};

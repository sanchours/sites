import React from 'react';
import { Form, Input } from 'antd';

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, disabled} = configField;
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
        })(<Input.Password disabled={!!disabled} />)
      }
    </Form.Item>
  )
};

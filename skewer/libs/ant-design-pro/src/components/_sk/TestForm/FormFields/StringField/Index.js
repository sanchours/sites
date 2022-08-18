import React from 'react';
import { Form, Input } from 'antd';

export default ({form, moduleData, configField, dispatch}) => {

  const {name, value, title, subtext, disabled} = configField;
  const {getFieldDecorator} = form;

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
          <Input disabled={!!disabled} />
        )
      }
    </Form.Item>
  );
}

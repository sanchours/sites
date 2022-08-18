import React from 'react';
import { Form, Input } from 'antd';

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, disabled, hideLabel} = configField;
  return (
    <Form.Item
      labelCol={{ span: 23 }}
      wrapperCol={{ span: 24 }}
      key={name}
      help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
      label={hideLabel ? '' : title}
    >
      {
        getFieldDecorator(name, {
          initialValue: value,
          rules: [{ required: false, message: '' }],
        })(<Input.TextArea disabled={!!disabled} rows={4} />)
      }
    </Form.Item>
  );
};

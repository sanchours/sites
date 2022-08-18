import React from 'react';
import { Form } from 'antd';

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, hideLabel} = configField;
  return (
    <Form.Item
      key={name}
      className="sk-form-link"
      help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
      label={hideLabel ? '' : title}
    >
      {
        getFieldDecorator(name, {
          rules: [{ required: false, message: '' }],
        })(<div dangerouslySetInnerHTML={{ __html: value }} />)
      }
    </Form.Item>
  )
}

import React from 'react';
import { Form, Input } from 'antd';
import styles from './styles.less'

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, disabled} = configField;
  return (
    <div className={styles.sk_hide_field}>
      <Form.Item
        key={name}
        help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
        label={title}
      >
        {
          getFieldDecorator(name, {
            initialValue: value,
            rules: [{ required: false, message: '' }],
          })(<Input type="hidden" disabled={!!disabled} />)
        }
      </Form.Item>
    </div>
  )
}

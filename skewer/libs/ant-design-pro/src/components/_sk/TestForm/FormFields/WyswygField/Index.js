import React from 'react';
import { Form } from 'antd';
import CKEditor from 'ckeditor4-react';

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, disabled, addConfig} = configField;
  const { NODE_ENV } = process.env;

  window.skWyswygConfig = addConfig;

  /**
   * Получить "обработанное" (подготовленное для отправки на сервер) значение
   * */
  const getHandledValue = (value) => {

    if ( typeof value === 'string' ){
      return value;
    }

    if (value && value.editor) {
      return value.editor.getData();
    }
    return '';

  };

  return (
    <Form.Item
      labelCol={{span: 23}}
      wrapperCol={{span: 24}}
      key={name}
      help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
      label={title}
    >
      {
        getFieldDecorator(name, {
          initialValue: value,
          handledValue: getHandledValue,
          rules: [{ required: false, message: '' }],
          getValueFromEvent: (e) => {

            if (e.name === 'change'){
              return e.editor.getData();
            }

            return e;

          },
          getValueProps: (value) => {
            return {
              data: value
            }
          }
        })(
          <CKEditor
            data={value}
            config={{
              customConfig: NODE_ENV === 'development' ? `/config_new.js` : `${ckedir}/config_new.js`,
              ...addConfig
            }}
          />
        )
      }
    </Form.Item>
  )
}

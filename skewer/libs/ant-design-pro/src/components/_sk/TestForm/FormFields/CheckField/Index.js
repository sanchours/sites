import React from 'react';
import { Form, Checkbox } from 'antd';
import styles from './style.less';

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, disabled} = configField;

  const getHandledValue = (val) => {
      return Number(val);
  };

  return (
    <>
      <Form.Item
        key={name}
        label={title}
        className={styles.Checkbox}
      >
        {
          getFieldDecorator(name, {
            initialValue: !!(value !== '0' && value),
            handledValue: getHandledValue,
            // normalize(v){
            //     return ;
            // },
            // getValueProps(val1){
            //     console.log(val.get('name'), !!(val1 !== "0" && val1));
            //     return {
            //         "data-__field": {
            //             name: val.get('name'),
            //             value: !!(val1 !== "0" && val1)
            //         },
            //         checked: !!(val1 !== "0" && val1),
            //         value: !!(val1 !== "0" && val1)
            //     };
            // },
            valuePropName: 'checked',
            rules: [{ required: false, message: '' }],
          })(<Checkbox disabled={!!disabled} />)
        }
      </Form.Item>
      {subtext ? <div className={styles.CheckboxInfo} dangerouslySetInnerHTML={{ __html: subtext }} /> : ''}
    </>
  )
}

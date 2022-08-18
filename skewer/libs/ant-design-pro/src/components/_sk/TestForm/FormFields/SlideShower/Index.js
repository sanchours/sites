import React from 'react';
import {Form} from 'antd';
import CKEditor from 'ckeditor4-react';
import styles from './style.less'

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, disabled} = configField;

  const getHandledValue = (value) => {
    let data = {};

    for(let i=1; i<=4; i += 1) {

      const container = document.getElementById(`ban_dd_lab_${i}`);

      // todo check
      // if ( block_x < 0 )
      //   block_x = -block_x;
      //
      // if ( block_x > image_w )
      //   block_x = parseInt( image_w * 0.8 );
      //
      // if ( block_y < 0 )
      //   block_y = -block_y;
      //
      // if ( block_y > image_h )
      //   block_y = parseInt( image_h * 0.8 );

      // this.ckeditorInstances[i].updateElement();
      data[`text${i}`] = document.querySelector(`#ban_dd_lab_${i} .cke_editable`).innerHTML;
      data[`text${i}_v`] = container.style.left;
      data[`text${i}_h`] = container.style.top;

    }

    return data;
  };


  const indexes = [1, 2, 3, 4];

  const textElements = indexes.map((index) =>
    <div
      id={`ban_dd_lab_${index}`}
      className={`banner__text${index}`}
      style={{
        left: `${value[`text${index}_h`]}px`,
        top: `${value[`text${index}_v`]}px`
      }}
    >
      <div className="builder-show-field">
        <CKEditor
          type="inline"
          contentEditable="true"
          data={value[`text${index}`]}
        />
      </div>
      <span className="js_sdd ban-dd">{`text${index}`}</span>
    </div>
  );
  return (
    <Form.Item
      key={name}
      help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
      label={title}
    >
      {
        getFieldDecorator(name, {
          initialValue: value,
          handledValue: getHandledValue,
          rules: [{ required: false, message: '' }],
        })(

          <div className="b-banner">
            <div className="banner__item">
              {textElements}
            </div>
            <img className="js_banner_bg" src={value.img} alt={value.title} />
          </div>
        )
      }
    </Form.Item>
  )
}

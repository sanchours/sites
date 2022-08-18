import React from 'react';
import {Button, Form, Input, Row, Col } from 'antd';
import * as sk from "../../../../../services/_sk/api";

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const { name, title, subtext, value, mode='editorMap', disabled } = configField;

  const {path} = moduleData;


  const handleButtonOpenMap = () => {

    const regex = /[^;]*;\s*\[id=([0-9]*)\]/gi;
    let geoObjectId='';

    let match;
    if ((match = regex.exec(value)) !== null) {
      if (match[1]) {
        geoObjectId = String(match[1]);
      }
    }

    //todo  Брать oldAdmin из buildConfig.files_path
    const href = `/oldadmin/?mode=${mode}&cmd=edit&mapMode=single&geoObjectId=${geoObjectId}&fieldName=${name}&path=${path}`;

    // открыть в новом окне
    sk.newWindow( href );


  };

  const handleButtonClean = () => {

    dispatch({
      type: 'skGlobal/updateForm2Param',
      payload: {
        path: path,
        fieldName: name,
        fieldValue: '',
      }
    });

  };


  return (
    <Form.Item
      key={name}
      help={<div dangerouslySetInnerHTML={{__html: subtext}} />}
      label={title}
    >
      <Row justify="space-between" gutter={16}>
        <Col span={14}>
          {
            getFieldDecorator(name, {
              initialValue: value,
              rules: [{ required: false, message: '' }],
            })(
              <Input disabled />
            )
          }
        </Col>

        <Col span={5}>
          <Button block onClick={handleButtonOpenMap} disabled={disabled}>
            {sk.dict('mapButtonText')}
          </Button>
        </Col>

        <Col span={5}>
          <Button block onClick={handleButtonClean} disabled={disabled}>
            {sk.dict('mapButtonClean')}
          </Button>
        </Col>

      </Row>
    </Form.Item>
  )
}

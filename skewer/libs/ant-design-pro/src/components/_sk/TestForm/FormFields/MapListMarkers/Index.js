import React from 'react';
import {Button, Form, Input, Row, Col } from 'antd';
import { isArray } from 'lodash';
import * as sk from "../../../../../services/_sk/api";

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const { name, title, subtext, groupTitle, value, mode='editorMap', disabled } = configField;

  const {path} = moduleData;


  const handleButtonOpenMap = () => {

    let entities = '';
    let showModification = 0;

    const items = moduleData.params.items;
    const filteredItems = items.filter( item => item.groupTitle === groupTitle);

    const {getFieldValue} = form;

    const indexFirstMultiSelect = filteredItems.findIndex(item => item.type === 'multiselect');
    if ( indexFirstMultiSelect !== -1 ){
      const firstMultiSelectField = filteredItems[indexFirstMultiSelect];
      const valueFirstMultiSelectField = getFieldValue(firstMultiSelectField.name);
      entities = isArray(valueFirstMultiSelectField) ? valueFirstMultiSelectField.join(',') : valueFirstMultiSelectField;
    }

    const indexFirstCheckbox = filteredItems.findIndex(item => item.type === 'check');
    if ( indexFirstCheckbox !== -1 ){
      const firstCheckBoxField = filteredItems[indexFirstCheckbox];
      const valueFirstCheckboxField = getFieldValue(firstCheckBoxField.name);
      showModification = Number(valueFirstCheckboxField);
    }

    const mapMode = 'list';
    const cmd = 'edit';

    //todo  Брать oldAdmin из buildConfig.files_path
    const href = `/oldadmin/?mode=${mode}&cmd=${cmd}&mapMode=${mapMode}&entities=${entities}&showModification=${showModification}&mapId=${value}&fieldName=${name}&path=${path}`;

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

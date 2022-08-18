import React from 'react';
import {Button, Form, Input, Row, Col } from 'antd';
import * as sk from "../../../../../services/_sk/api";

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {
    name,
    value='0',
    gal_profile_id:GalProfileId='0',
    seoClass='',
    iEntityId=0,
    sectionId=0,
    mode='galleryBrowser',
    title,
    subtext,
    disabled
  } = configField;

  const {path} = moduleData;

  const parseUrl = name => {
    const escapedName = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
    const regexS = `[\\?&\\.]${escapedName}=([^&#\/]*)`;
    const regex = new RegExp( regexS );
    const results = regex.exec( window.location.href );
    if (results == null) {
      return '';
    }
    return results[1];
  };

  const handleButtonGallery = () => {

    let makeNewAlbum = 0;

    const selectMode = mode;
    let selectValue = value;
    if ( !selectValue ) {
      selectValue = 0;
      makeNewAlbum = 1;
    }

    // собрать ссылку
    const href = `/oldadmin/?mode=${selectMode}&cmd=showAlbum&gal_album_id=${selectValue}&gal_profile_id=${GalProfileId}&gal_new_album=${makeNewAlbum}&seoClass=${seoClass}&iEntityId=${iEntityId}&sectionId=${sectionId}&fieldName=${name}&path=${path}`;

    // открыть в новом окне
    sk.newWindow( href );

    return true;

  };

  const handleButtonRecreate = () => {

    const makeNewAlbum = 1;

    let selectValue = parseInt(value);
    if ( !selectValue ) {
      selectValue = 0;
    }

    // собрать ссылку
    const href = `/oldadmin/?mode=${mode}&cmd=showAlbum&gal_album_id=${selectValue}&gal_profile_id=${GalProfileId}&gal_new_album=${makeNewAlbum}&seoClass=${seoClass}&iEntityId=${iEntityId}&sectionId=${sectionId}&fieldName=${name}&path=${path}`;

    if (selectValue) {
      sk.showModal(<div dangerouslySetInnerHTML={{ __html: sk.dict('confirmHeader') }} />, <div dangerouslySetInnerHTML={{ __html: sk.dict('galleryBrowserNewConfirm') }} />, () => {
        sk.newWindow( href );
      });
    } else {
      sk.newWindow( href );
    }

    return true;

  };

  return (
    <Form.Item
      key={name}
      help={<div dangerouslySetInnerHTML={{__html: subtext}} />}
      label={title}
    >
      <Row justify="space-between" gutter={16}>
        <Col sm={14} xs={24}>
          {
            getFieldDecorator(name, {
              initialValue: value,
              rules: [{ required: false, message: '' }],
            })(
              <Input disabled />
            )
          }
        </Col>

        <Col sm={5} xs={24}>
          <Button block onClick={handleButtonGallery} disabled={disabled}>
            {sk.dict('fileBrowserSelect')}
          </Button>
        </Col>

        <Col sm={5} xs={24}>
          <Button block onClick={handleButtonRecreate} disabled={disabled}>
            {sk.dict('galleryBrowserNew')}
          </Button>
        </Col>

      </Row>
    </Form.Item>
  )
}

import React from 'react';
import {Button, Form, Icon, Input, message, Upload, Row, Col} from 'antd';
import * as sk from "../../../../../services/_sk/api";
import styles from './style.less'

export default ({form, moduleData, configField, dispatch}) => {
  const {getFieldDecorator} = form;
  const {name, value, title, subtext, disabled} = configField;
  const {path, moduleLayer, moduleName} = moduleData;

  const handleOnChange = (info) => {

    if (info.file.status === 'done') {

      if (info.file.response.success) {
        message.success(sk.dict('fileUploadedSuccessfully').replace('{filename}', info.file.name));

        dispatch({
          type: 'skGlobal/updateForm2Param',
          payload: {
            path: path,
            fieldName: name,
            fieldValue: info.file.response.file,
          }
        });

      }

    } else if (info.file.status === 'error') {
      message.error(sk.dict('errorLoadingFile').replace('{filename}', info.file.name));
    }
  };

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

  const iSectionId = parseUrl('section');
  let folderAlias = moduleData.params.serviceData._filebrowser_section || '';

  if (!folderAlias && moduleLayer && moduleName) {
    folderAlias = `${moduleLayer}_${moduleName}`;
  }

  const handlePopupButton = () => {

    const selectMode = 'fileBrowser';

    // собрать ссылку
    const href = `/oldadmin/?mode=${selectMode}&type=file&returnTo=fileSelector&section=${iSectionId}&fieldName=${name}&path=${path}&module=${folderAlias}`;

    // открыть в новом окне
    sk.newWindow(href);

    return true;

  };

  const props = {
    name: 'uploadFile[]',
    action: '/ajax/uploader.php',
    multiple: false,
    showUploadList: false,
    headers: {
      authorization: 'authorization-text',
    },
    data: {
      cmd: 'uploadImage',
      section: iSectionId,
      folder_alias: folderAlias,
    },

  };

  return (
    <Form.Item
      labelCol={{span: 23}}
      wrapperCol={{span: 24}}
      key={name}
      help={<div dangerouslySetInnerHTML={{__html: subtext}}/>}
      label={title}
    >
      <Row justify="space-between" gutter={16}>
        <Col sm={14} xs={24}>
          {
            getFieldDecorator(name, {
              initialValue: value,
              rules: [{ required: false, message: '' }],
            })(
              <Input disabled={disabled} />
            )
          }
        </Col>
        <Col sm={5} xs={24}>
          <Button onClick={handlePopupButton} disabled={disabled} block>
            {sk.dict('fileBrowserSelect')}
          </Button>
        </Col>
        <Col sm={5} xs={24}>
          <Upload
            className={styles.Upload}
            disabled={disabled}
            {...props}
            onChange={handleOnChange}
          >
            <Button block>
              <Icon type="upload" /> {sk.dict('upload')}
            </Button>
          </Upload>
        </Col>
      </Row>
    </Form.Item>
  )
}

import React, {useState} from 'react';
import {connect} from "dva";
import {Button, Form, Icon, Upload} from "antd";
import TestPanel from "../TestPanel/Index";
import * as sk from "../../../services/_sk/api";

const Content = ({form, moduleData, dispatch}) => {

  const [uploadFileList, setUploadFileList] = useState([]);
  const [uploadFiles, setUploadFiles] = useState([]);

  const normFile = e => {

    if (Array.isArray(e)) {
      return e;
    }
    return e && e.fileList;
  };


  const {getFieldDecorator} = form;

  const serviceData = moduleData.getIn(['params', 'serviceData']).toJS();

  const uploadFieldConfig = {
    name: 'file0[]',
    action: '/admin/index.php',
    multiple: 'multiple',
    data: {
      ...serviceData,
      cmd: 'upload',
      sessionId: window.sessionId,
      path: moduleData.get('path'),
    },
    onChange(info) {
      let files = uploadFiles;
      if (info.file.status === 'done' && info.file.response.success || info.file.status === 'error') {
        files = [info.file.response.data[0], ...uploadFiles];
        setUploadFiles(files);
      }

      if (files.length === uploadFileList.length) {
        files[0].params.loadedFiles = files.map((file) => {
          return file.params.loadedFiles[0];
        });
        setUploadFiles([]);
        setUploadFileList([]);
        dispatch({
          type: 'skGlobal/updateListFiles',
          payload: files
        });
      }
    },
    beforeUpload(file, fileList) {
      setUploadFileList(fileList);
    }
  };

  return (
    <Form
      hideRequiredMark
      labelAlign='left'
      colon={false}
    >
      <Form.Item
        label={moduleData.getIn(["init", 'lang', 'fileBrowserFile'])}
      >
        {
          getFieldDecorator('file', {
            valuePropName: "fileList",
            getValueFromEvent: normFile,
            initialValue: [],
            rules: [{ required: true, message: '' }],
          })(

            <Upload {...uploadFieldConfig}>
              <Button>
                <Icon type="upload" /> {sk.dict('upload')}
              </Button>
            </Upload>
          )
        }
      </Form.Item>
    </Form>
  );

};

const FileAddForm = ({moduleData, form, dispatch}) => {

  const handleClickOnButton = (configButton) => () => {

    const {state, action} = configButton;

    switch (state) {
      case 'upload':
        form.validateFields((err, values) => {
          if (!err) {
            console.log('Received values of form: ', values);
          }
        });
        break;

      default:

        dispatch({
          type: 'skGlobal/handleClickOnButton',
          payload: {
            path: moduleData.get('path'),
            action
          },
        });

    }

  };

  return (
    <TestPanel
      title={moduleData.getIn(['params', 'panelTitle'])}
      buttonsData={moduleData.getIn(['params', 'dockedItems', 'left'])}
      handleClickOnButton={handleClickOnButton}
    >
      <Content form={form} moduleData={moduleData} dispatch={dispatch} />
    </TestPanel>
  );
};

const FormWrapped = Form.create({name: 'test_form'})(FileAddForm);

export default connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.tabs.get('out.tabs.lib_files'),
  };
})(FormWrapped)

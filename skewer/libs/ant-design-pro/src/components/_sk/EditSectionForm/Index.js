import React from 'react';
import { Form, Input, Modal, Select } from 'antd';
import { connect } from 'dva';
import * as sk from "../../../services/_sk/api";

const EditSectionForm = ( {editingSectionForm, hideWindow, expandedKeys, parentModulePath, form, dispatch, sections} ) => {

  const { getFieldDecorator } = form;

  const langValues = editingSectionForm.getIn(['init', 'lang']);

  const formData = editingSectionForm.getIn(['params', 'form']);

  if (!formData) {
    return '';
  }

  const isFormAdd = !parseInt(formData.get('id'));

  const visible = Boolean(editingSectionForm.get("isFetchReady") && editingSectionForm.get("isShowModal"));

  // todo Протянуть с сервера ['init', 'lang', 'treeFormHeaderUpd']
  return (
    <Modal
      title={
        isFormAdd
          ? langValues.get('treeFormHeaderAdd')
          : langValues.get('treeFormHeaderUpd', sk.dict("sectionEditing"))
      }
      visible={visible}
      align="center"
      okText={langValues.get('paramFormSaveUpd')}
      cancelText={langValues.get('paramFormClose')}
      width={480}
      onOk={() => {
        const item = form.getFieldsValue();

        dispatch({
          type: 'skGlobal/saveSection',
          payload: {
            path: parentModulePath,
            item,
          },
        });

        if (item.parent) {
           if (!Object.keys(sections).includes(item.parent.toString())) {
             dispatch({
               type: 'skGlobal/getTree',
               payload: {
                 path: parentModulePath,
                 sectionId: item.parent
               }
             });
           }

          dispatch({
            type: 'skGlobal/extendedTree',
            payload: {
              expandedKeys: !expandedKeys.includes(item.parent)
                ? expandedKeys.concat([item.parent])
                : expandedKeys,
              path: parentModulePath
            }
          });
        }

        // сбрасываем поля
        form.resetFields();

        // скрыть модальное окно
        hideWindow();
      }}
      onCancel={() => {

        // сбрасываем поля
        form.resetFields();

        // скрыть модальное окно
        hideWindow();
      }}
    >
      <Form
        {...{
          hideRequiredMark: true,
          labelCol: { span: 8 },
          wrapperCol: { span: 16 },
          labelAlign: 'left',
        }}
      >
        <Form.Item key="id" style={{ display: 'none' }}>
          {getFieldDecorator('id', {
            initialValue: formData.get('id'),
            rules: [{ required: false, message: '' }],
          })(<Input type="hidden" />)}
        </Form.Item>

        <Form.Item key="title" label={langValues.get('treeFormTitleTitle')}>
          {getFieldDecorator('title', {
            initialValue: formData.get('title'),
            rules: [{ required: false, message: '' }],
          })(<Input />)}
        </Form.Item>

        <Form.Item key="alias" label={langValues.get('treeFormTitleAlias')}>
          {getFieldDecorator('alias', {
            initialValue: formData.get('alias'),
            rules: [{ required: false, message: '' }],
          })(<Input />)}
        </Form.Item>

        <Form.Item key="parent" label={langValues.get('treeFormTitleParent')}>
          {getFieldDecorator('parent', {
            initialValue: formData.get('parent'),
            rules: [{ required: false, message: '' }],
          })(
            <Select>
              {formData.get('parent_list').map(val => {
                return (
                  <Select.Option key={val.get('id')} value={val.get('id')}>
                    {val.get('title')}
                  </Select.Option>
                );
              })}
            </Select>
          )}
        </Form.Item>

        <Form.Item key="template" label={langValues.get('treeFormTitleTemplate')}>
          {getFieldDecorator('template', {
            initialValue: formData.get('template'),
            rules: [{ required: false, message: '' }],
          })(
            <Select disabled={!isFormAdd}>
              {formData.get('template_list').map(val => {
                return (
                  <Select.Option
                    key={parseInt(val.get('id'), 10)}
                    value={parseInt(val.get('id'), 10)}
                  >
                    {val.get('title')}
                  </Select.Option>
                );
              })}
            </Select>
          )}
        </Form.Item>

        <Form.Item key="link" label={langValues.get('treeFormTitleLink')}>
          {getFieldDecorator('link', {
            initialValue: formData.get('link'),
            rules: [{ required: false, message: '' }],
          })(<Input />)}
        </Form.Item>

        <Form.Item key="visible" label={langValues.get('treeTitleVisible')}>
          {getFieldDecorator('visible', {
            initialValue: formData.get('visible'),
            rules: [{ required: false, message: '' }],
          })(
            <Select>
              <Select.Option value={1}>{langValues.get('visibleVisible')}</Select.Option>
              <Select.Option value={0}>{langValues.get('visibleHiddenFromMenu')}</Select.Option>
              <Select.Option value={2}>{langValues.get('visibleHiddenFromPath')}</Select.Option>
              <Select.Option value={3}>{langValues.get('visibleHiddenFromIndex')}</Select.Option>
            </Select>
          )}
        </Form.Item>
      </Form>
    </Modal>
  );
};

const FormWrapped = Form.create()(EditSectionForm);

export default connect(({ skGlobal }) => {
  return {
    editingSectionForm: skGlobal.editingSectionForm,
  };
})(FormWrapped);

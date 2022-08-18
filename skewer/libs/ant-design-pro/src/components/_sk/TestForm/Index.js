import React from 'react';
import { connect } from 'dva';
import { Divider, Form } from 'antd';
import CollapsiblePanels from '../CollapsiblePanels/Index';
import { createFormField } from './utils/formHelper';
import Styles from './Index.less';
import TestPanel from '../TestPanel/Index';
import * as sk from "../../../services/_sk/api";

const normalizeFieldsData = (dataObject) => {
  let outDataObject = {};

  Object.entries(dataObject).forEach(([name, value]) => {
    //  для имен переменный начинающихся с точки
    if (name === '' && sk.isObject(value)) {
      const [nameFromObject, valueFromObject] = Object.entries(value)[0];

      name = "." + nameFromObject;
      value = valueFromObject;
    }

    outDataObject = {
      ...outDataObject,
      [name]: value
    };
  });

  return outDataObject;
}

const getDataFromForm = (form) => {
  const {getFieldsValue, getFieldProps} = form;

  let handledFormData = {};

  Object.entries(normalizeFieldsData(getFieldsValue())).forEach(([name, value]) => {
    handledFormData = {
      ...handledFormData,
      [name]: (getFieldProps(name)['data-__meta'].handledValue !== undefined)
        ? getFieldProps(name)['data-__meta'].handledValue(value)
        : value
    }

  });

  return handledFormData;
};


const addText = (props) => {
  const { moduleData } = props;
  const text = moduleData.getIn(['params', 'addText']);

  return text ? (
    <div>
      <div dangerouslySetInnerHTML={{ __html: text }} />
      <Divider />
    </div>
  ) : (
    ''
  );
};


export default
connect(({ skGlobal, loading }, { tabKey }) => {
  return {
    moduleData: skGlobal.tabs.get(tabKey),
    tabKey
  };
})(
  Form.create({
    name: 'horizontal_login',
    mapPropsToFields(props) {

      const {moduleData} = props;

      const mapNameToValue = {};

      moduleData.getIn(['params', 'items'], []).toJS().forEach(val => {

        let {value} = val;

        switch (val.type){
          case 'check':
            value = Number(val.value);
            break;
          case 'select':
            const showValKeys = Object.keys(val.show_val);
            if (!val.emptyStr && val.value === '' && showValKeys.length !== 0) {
              value = showValKeys[0]; // если у списка не выставлено значени, то берем первое из списка
            }
            break;
          case 'gallery':
            if (val.value === 0) {
              val.value = "0";
            }
            break;
          default:
            if (!val.value) {
              value = val.type === 'num' ? 0 : '';
            }
        }

        mapNameToValue[val.name] = Form.createFormField({value});
      });

      return mapNameToValue;
    },
    onFieldsChange(props, changedValues) {
      const { moduleData, dispatch, form } = props;
      const handledFormData = getDataFromForm(form);
      const values = normalizeFieldsData(form.getFieldsValue());
      const changedValuesData = normalizeFieldsData(changedValues);
      // Форма редактировалась?
      const formIsDirty = form.isFieldsTouched(Object.keys(values));

      // Обновляем список грязных форм
      dispatch({
        type: 'skGlobal/updateDirtyFormsList',
        payload: {
          formName: moduleData.get('path'),
          isDirty: formIsDirty
        }
      });

      Object.entries(changedValuesData).forEach(([nameField, configField]) => {
        dispatch({
          type: 'skGlobal/updateForm',
          payload: {
            path: moduleData.get('path'),
            fieldName: nameField,
            fieldValue: configField.value,
            changedValues: changedValuesData,
            values: handledFormData,
          },
        });
      });
    }
  })(
    (props) => {

      const { tabKey: formClassName, moduleData, form, dispatch } = props;

      const handleClickOnButton = (configButton) => () => {

        const {
          action='',
          state='',
          confirmText='',
          actionText=''
        } = configButton;

        if (action) {

          let handledFormData = getDataFromForm(form);

          const data4Sending = {
            action,
            path: moduleData.get('path'),
            formData: handledFormData,
            buttonData: configButton,
            from: 'form',
          };

          const dispatchConfig = {
            type: 'skGlobal/handleClickOnButton',
            payload: data4Sending,
          };

          switch (state) {
            case 'delete':

              let rowText = '';

              const item = form.getFieldsValue();

              if ( item )
                rowText = item.title || item.name || '';

              if ( !rowText )
                rowText = sk.dict('delRowNoName');

              const sHeader = sk.dict('delRowHeader');

              const sText = sk.dict('delRow').replace('{0}', rowText);

              sk.showModal(<div dangerouslySetInnerHTML={{ __html: sHeader }} />, <div dangerouslySetInnerHTML={{ __html: sText }} />, () => {
                dispatch(dispatchConfig);
              });

              break;


            case 'allow_do':

              sk.showModal(<div dangerouslySetInnerHTML={{ __html: sk.dict('allowDoHeader') }} />, <div dangerouslySetInnerHTML={{ __html: actionText }} />, () => {
                dispatch(dispatchConfig);
              });

              break;

            default:

              // Требуется подтверждение?
              if ( confirmText ) {

                sk.showModal(<div dangerouslySetInnerHTML={{ __html: sk.dict('allowDoHeader') }} />, <div dangerouslySetInnerHTML={{ __html: confirmText }} />, () => {
                  dispatch(dispatchConfig);
                });

              } else {

                // Обычная отправка
                dispatch(dispatchConfig);

              }

          }
        }

      };

      const getReactFieldByType = val => {
        return createFormField({
          form,
          formClassName,
          dispatch,
          moduleData: moduleData.toJS(),
          configField: val.toJS(),
        });
      };

      const fields = moduleData.getIn(['params', 'items'], []);

      const FormFields = fields
        .filter(item => !item.get('hidden'))
        .map(val => {
        return {
          formItem: getReactFieldByType(val),
          data: val
        };
      });

      let groups = {};
      const elements = [];

      FormFields.forEach(value => {
        const groupTitle = value.data.get('groupTitle');

        if (groupTitle) {
          // Добавка приставки к числовым названиям групп, что бы js не отсортировал их автоматически
          const groupIndex = `g${groupTitle}`;

          // Добавляем группу, если её нет
          if (groups[groupIndex] === undefined) {
            groups = {
              ...groups,
              [groupIndex]: {
                groupTitle,
                collapsible: false,
                collapsed: false,
                items: []
              }
            };
          }

          groups[groupIndex].items.push(value.formItem);

          if (value.data.get('groupType') !== undefined) {
            /* Добавление кнопки сворачивания группы, если хотя бы у одного поля определён параметр groupType */
            // Показ кнопки сворачивания группы
            groups[groupIndex].collapsible = value.data.get('groupType');
            // Состояние группы. True = свёрнута
            groups[groupIndex].collapsed = +(value.data.get('groupType')) === 2;
          }
        } else {
          elements.push(value.formItem);
        }
      });

      elements.push(
        <CollapsiblePanels
          key="collapsiblePanel"
          groups={groups}
        />
      );

      return (
        <Form
          className={formClassName}
          hideRequiredMark={true}
          labelAlign='left'
          colon={false}
        >
          <TestPanel
            title={moduleData.getIn(['params', 'panelTitle'])}
            buttonsData={moduleData.getIn(['params', 'dockedItems', 'left'])}
            handleClickOnButton={handleClickOnButton}
          >
            <div className={Styles.form}>
              {addText(props)}
              {elements}
            </div>
          </TestPanel>
        </Form>
      );

    }
  )
);

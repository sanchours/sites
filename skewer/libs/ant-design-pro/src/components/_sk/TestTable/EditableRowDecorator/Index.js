import React, {useContext, useEffect, useState} from 'react';
import { Input, Checkbox, Form, InputNumber } from 'antd';

const FormItem = Form.Item;

const EditableContext = React.createContext('123');

const EditableRow = ({ form, index, ...props }) => {
  return (
    <EditableContext.Provider value={form}>
      <tr {...props} />
    </EditableContext.Provider>
  );
};

const EditableDecorator = OriginalComponent => ({ form, ...props }) => {
  return (
    <EditableContext.Provider value={form}>
      <OriginalComponent {...props} />
    </EditableContext.Provider>
  );
};

const EditableCell = ({ record, handleSave, listSaveCmd, width, jsView, editing, dataIndex, children, setEditingKey, ...restProps }) => {
  const form = useContext(EditableContext);
  const [isEditing, setEditing] = useState(false);

  const getTitle = () => {
    const title = children[2];
    if (typeof(title) === "string") {
      return title
    }
    return null
  };

  const toggleEdit = () => {
    const isEdit = !isEditing;
    setEditing(isEdit);
  };

  const save = (fieldName, str) => (e) => {

    form.validateFields((error, values) => {
      if (error) {
        return;
      }

      toggleEdit();

      if (values.title !== undefined && values.title.trim() === record.title.trim()) {
        values.title = values.title.trim();
        setEditingKey('');
        return false;
      }

      let data = { ...record, ...values };
      delete data.key;

      handleSave(
        data,
        fieldName,
        listSaveCmd
      );

    });

    return false;

  };

  const renderElem = () => {
    let elem;
    const commonConfig = {
      onBlur: save(dataIndex),
      onPressEnter: save(dataIndex),
    };

    const eStop = (e) => {
      e.persist();
      e.stopPropagation();
      e.nativeEvent.stopImmediatePropagation();
    };

    const eventStop = {
      onClick: (e) => {
        eStop(e)
      },
      onDoubleClick: (e) => {
        eStop(e)
      },
      onMouseMove: (e) => {
        eStop(e)
      }
    };

    if ( editing ){
      switch (jsView) {
        case 'money':
        case 'float':
          elem = (
            <FormItem style={{ margin: 0 }} {...eventStop}>
              {form.getFieldDecorator(dataIndex, {
                rules: [{ required: false, message: `` }],
                initialValue: record[dataIndex],
              })(
                <InputNumber
                  {...commonConfig}
                  min={0}
                  step={1}
                />
              )}
            </FormItem>
          );

          break;

        case 'num':
          elem = (
            <FormItem style={{ margin: 0 }}>
              {form.getFieldDecorator(dataIndex, {
                rules: [{ required: false, message: `` }],
                initialValue: record[dataIndex],
              })(
                <InputNumber {...commonConfig}  />
              )}
            </FormItem>
          );

          break;

        case 'str':
        default:
          elem = (
            <FormItem style={{ margin: 0 }}>
              {form.getFieldDecorator(dataIndex, {
                rules: [{ required: false, message: `` }],
                initialValue: record[dataIndex],
              })(
                <Input {...commonConfig} />
              )}
            </FormItem>
          );

          break;

      }

    } else {

      if ( jsView === 'check' ){

        elem = (
          <Checkbox
            checked={record[dataIndex] !== '0' && record[dataIndex]}
            onChange={(e) => {

              form.validateFields((error, values) => {
                if (error) {
                  return;
                }

                record[dataIndex] = Number(e.target.checked);

                let data = { ...record, ...values, ...{[dataIndex]: Number(e.target.checked)} };
                delete data.key;

                handleSave(
                  data,
                  dataIndex,
                  listSaveCmd
                );

              });

            }}
            className="sk-table_checkbox"
          />
        );

      } else {

        if (!isEditing) {
          elem = (
            <div onClick={toggleEdit}>
              {children}
            </div>
          )
        } else {
          elem = children
        }

      }

    }


    return elem;

  };

  return (
    <td /*title={title}*/ {...restProps} style={{ maxWidth: width }}>
      {renderElem()}
    </td>
  );

};


export { EditableCell, EditableRow, EditableDecorator };

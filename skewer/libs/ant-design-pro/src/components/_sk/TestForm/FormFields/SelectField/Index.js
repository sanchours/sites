import React, {useEffect, useState} from 'react';
import { Form, Select } from 'antd';

const emptyStrValInSelect = '';

export default ({form, formClassName, configField}) => {
  const {getFieldDecorator} = form;
  const {name, type, title, subtext, disabled, emptyStr, valueField, displayField, store, disabledVariants, editable, show_val, value} = configField;
  const {data} = store;

  const [currentValue, setCurrentValue] = useState(undefined);

  useEffect(() => {
    if (!show_val.hasOwnProperty(currentValue) && currentValue !== undefined) {
      setCurrentValue(value);
    }
    return () => {setCurrentValue(undefined)};
  }, [show_val]);

  const getSelectMode = (selectType) => {

    if (selectType === "multiselect") {
      return "multiple";
    }

    if (editable) {
      return "combobox";
    }

    return "default";
  }

  const getHandledValue = (value) => {
    if ( value === emptyStrValInSelect || value === false ){
      return '';
    }
    return _.isArray(value) ? value.join(',') : value
  };

  const getValueProps = (value) => {

    let handledValue = currentValue !== undefined
      ? currentValue
      : value;

    if ( emptyStr && !handledValue ){
      return {
        value: emptyStrValInSelect
      };
    }

    if (typeof handledValue === "boolean") {
      return {
        value: emptyStrValInSelect
      };
    }

    handledValue = !_.isArray(handledValue)
      ? _.compact(handledValue.toString().split(','))
      : handledValue;

    // Приведение элементов массива к строке
    handledValue = handledValue.map(val => val.toString());

    return {
      value: handledValue
    }

  };

  const getValueFromEvent = (value) => {
    setCurrentValue(value);

    return value;
  }

  if (emptyStr){
    const emptyStrIndex = data.findIndex(item => item[displayField] === '---');
    if ( emptyStrIndex !== -1 ){
      data[emptyStrIndex][valueField] = emptyStrValInSelect;
    }
  }

  const options = data
    .map(dataVal => {

      let disabled = false;
      let position = -1;

      if (disabledVariants) {
        position = disabledVariants.findIndex(
          disabledValue => disabledValue === dataVal[valueField]
        );
        disabled = position !== -1;
      }

      return (
        <Select.Option
          disabled={disabled}
          key={dataVal[displayField].toString()}
          label={dataVal[displayField].toString()}
          value={dataVal[valueField].toString()}
        >
          <div dangerouslySetInnerHTML={{ __html: dataVal[displayField] }} />
        </Select.Option>
      );
    });

  return (
    <Form.Item
      key={name}
      help={<div dangerouslySetInnerHTML={{ __html: subtext }} />}
      label={title}
    >
      {
        getFieldDecorator(name, {
          getValueProps,
          getValueFromEvent,
          handledValue: getHandledValue,
          rules: [{ required: false, message: '' }],
        })(
          <Select
            mode={getSelectMode(type)}
            disabled={!!disabled}
            getPopupContainer={() => document.getElementsByClassName(formClassName)[0]}
            className={type === 'multiselect' ? 'ant-select-multiselect' : ''}
            optionFilterProp="label"
          >
            {options}
          </Select>
        )
      }
    </Form.Item>
  )
}

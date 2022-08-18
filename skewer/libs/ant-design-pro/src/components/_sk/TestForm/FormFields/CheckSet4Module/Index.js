import React from "react";
import {Divider} from 'antd'
import CollapsiblePanels from '../../../CollapsiblePanels/Index'
import CheckField from '../CheckField/Index'
import styles from './Index.less'
import * as sk from "../../../../../services/_sk/api";

export default ({configField, ...rest}) => {

  const { name: nameGroupField, title, items } = configField;

  const groupsItems = items.map(({name, execute, ...otherProps}) => {
    const config = {
      name: `${nameGroupField}_${name}`,
      ...otherProps
    };

    const setAllFormFields = (value) => {
      let fieldsValues = {};

      items.forEach(({name}) => {
        fieldsValues[`${nameGroupField}_${name}`] = value;
      });

      rest.form.setFieldsValue(fieldsValues);
    };

    if ( execute ){
      return (
        <div className={styles.SetAllBlock}>
          <a onClick={() => setAllFormFields(1)}> {sk.dict("installAll")}</a>
          <Divider type="vertical" />
          <a onClick={() => setAllFormFields(0)}> {sk.dict("resetAll")}</a>
        </div>
      )
    }

    return (
      <CheckField
        configField={config}
        {...rest}
      />
    )
  });

  return (
    <CollapsiblePanels
      groups={[{collapsible: true, collapsed: false, groupTitle: title, items: groupsItems}]}
    />
  )
};

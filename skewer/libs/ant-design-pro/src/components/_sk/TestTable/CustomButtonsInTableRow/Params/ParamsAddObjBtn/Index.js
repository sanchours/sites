import React from 'react';
import { Tooltip } from 'antd';
import Icon from "../../../../Icon/Index";
import * as sk from '../../../../../../services/_sk/api'

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  // record = {group: ".", id: "1508", key: "1508", name: "layout", parent: "78", show_val: "", title: "", value: ""}

  if (record.name === 'object' || record.name === 'objectAdm'){
    dispatch({
      type: 'skGlobal/handleCustomButtonInParameters',
      payload: {
        dispatchCmd: 'handleAddObjBtn',
        path: mainContainerData.get('path'),
        data: _.merge(
          {
            cmd: 'addByTemplate',
            data: {
              id: record.id,
              type: record.name,
              parent: record.parent
            }
          },
          mainContainerData.getIn(['params', 'serviceData']).toJS()
        )
      }
    });
  } else {
    sk.error(mainContainerData.getIn(['init', 'dict', 'addByTemplateErrorMessages']));
  }
};

export default (props) => {

  const {record, mainContainerData} = props;

  if (record.name !== 'object' && record.name !== 'objectAdm'){
    return null;
  }

  return (
    <Tooltip
      placement="bottomRight"
      title={mainContainerData.getIn(['params', 'init', 'lang', 'upd'])}
    >
      <span>
        <Icon
          alias='icon-configuration'
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};

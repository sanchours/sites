import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  // record = {group: ".", id: "1508", key: "1508", name: "layout", parent: "78", show_val: "", title: "", value: ""}

  dispatch({
    type: 'skGlobal/handleCustomButtonInParameters',
    payload: {
      dispatchCmd: 'unsetOverride',
      path: mainContainerData.get('path'),
      data: _.merge(
        {
          cmd: 'unsetOverride',
          data: {
            language: record.language,
            category: record.category,
            message: record.message
          }
        },
        mainContainerData.getIn(['params', 'serviceData']).toJS()
      )
    }
  });


};

export default (props) => {

  const {record} = props;

  if ( !parseInt(record.override) ){
    return null;
  }

  return (
    <Tooltip
      placement="bottomRight"
    >
      <span>
        <Icon
          alias='icon-reload'
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};

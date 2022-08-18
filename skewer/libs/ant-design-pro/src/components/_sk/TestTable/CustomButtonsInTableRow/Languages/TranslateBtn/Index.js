import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";

const statusNotTranslated = 0;
const statusTranslated = 1;
const statusInProcess = 2;

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  // record = {group: ".", id: "1508", key: "1508", name: "layout", parent: "78", show_val: "", title: "", value: ""}

  if ( parseInt(record.status) !== statusNotTranslated ){
    return null;
  }

  dispatch({
    type: 'skGlobal/handleCustomButtonInParameters',
    payload: {
      dispatchCmd: 'translate',
      path: mainContainerData.get('path'),
      data: _.merge(
        {
          cmd: 'translate',
          data: {
            ...record,
          }
        },
        mainContainerData.getIn(['params', 'serviceData']).toJS()
      )
    }
  });

};

export default (props) => {

  const {record} = props;

  if ( parseInt(record.status) !== statusNotTranslated ){
    return null
  }

  return (
    <Tooltip
      placement="bottomRight"
    >
      <span>
        <Icon
          alias='icon-visible'
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};

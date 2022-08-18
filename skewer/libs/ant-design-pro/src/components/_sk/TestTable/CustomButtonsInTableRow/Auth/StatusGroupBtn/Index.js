import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";

const statusNoAuth = 0;
const statusAuth = 1;
const statusBanned = 2;

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  // record = {group: ".", id: "1508", key: "1508", name: "layout", parent: "78", show_val: "", title: "", value: ""}

  let newStatus;

  const status = parseInt(record.active);

  switch ( status ) {
    case statusAuth:
      newStatus = statusBanned;
      break;
    case statusBanned:
      newStatus = statusAuth;
      break;
    case statusNoAuth:
    default :
      newStatus = statusAuth;
      break;
  }

  dispatch({
    type: 'skGlobal/handleCustomButtonInParameters',
    payload: {
      dispatchCmd: 'changeStatus',
      path: mainContainerData.get('path'),
      data: _.merge(
        {
          cmd: 'changeStatus',
          data: {
            ...record,
            active: newStatus
          }
        },
        mainContainerData.getIn(['params', 'serviceData']).toJS()
      )
    }
  });


};

export default (props) => {

  const {record} = props;

  let iconAlias;

  switch ( parseInt(record.active) ) {
    case statusAuth:
      iconAlias = 'icon-saved';
      break;
    case statusBanned:
      iconAlias = 'icon-stop';
      break;

    case statusNoAuth:
    default :
      iconAlias = 'icon-upgrade';
  }

  return (
    <Tooltip
      placement="bottomRight"
    >
      <span>
        <Icon
          alias={iconAlias}
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};

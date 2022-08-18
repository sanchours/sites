import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";

const New = 0;
const Active = 1;
const NoActive = 2;

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  // record = {group: ".", id: "1508", key: "1508", name: "layout", parent: "78", show_val: "", title: "", value: ""}

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
            status: Active
          }
        },
        mainContainerData.getIn(['params', 'serviceData']).toJS()
      )
    }
  });


};

export default (props) => {

  const {record, configButton} = props;
  const {tooltip} = configButton;

  if ( (parseInt(record.status) !== New) && (parseInt(record.status) !== NoActive) ){
    return null;
  }

  return (
    <Tooltip
      placement="bottomRight"
      title={tooltip}
    >
      <span>
        <Icon
          alias='icon-saved'
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};

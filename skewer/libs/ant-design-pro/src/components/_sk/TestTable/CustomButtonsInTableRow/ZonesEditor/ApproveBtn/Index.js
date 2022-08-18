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
      dispatchCmd: 'toogleActivity',
      path: mainContainerData.get('path'),
      data: _.merge(
        {
          cmd: 'toogleActivity',
          data: {
            ...record,
            bUse: !record.useInZone
          }
        },
        mainContainerData.getIn(['params', 'serviceData']).toJS()
      )
    }
  });


};

export default (props) => {

  const {record, mainContainerData} = props;

  let tooltipText;
  let iconAlias;

  if (record.useInZone !== true && record.useInZone !== false){
    return null;
  }

  if (record.useInZone){
    tooltipText = mainContainerData.getIn(['params', 'init', 'lang', 'btnRow_disableModule']);
    iconAlias = 'icon-stop';
  } else {
    tooltipText = mainContainerData.getIn(['params', 'init', 'lang', 'btnRow_enableModule']);
    // this.draggable = false;
    iconAlias = 'icon-saved';
  }


  return (
    <Tooltip
      placement="bottomRight"
      title={tooltipText}
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

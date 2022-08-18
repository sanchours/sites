import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  dispatch({
    type: 'skGlobal/handleCustomButtonInParameters',
    payload: {
      dispatchCmd: 'deleteOrCopy',
      path: mainContainerData.get('path'),
      data: _.merge(
        {
          cmd: 'deleteOrCopy',
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

  const {record, mainContainerData} = props;

  let tooltipText;
  let iconAlias;

  if (record.inherited !== true && record.inherited !== false){
    return null;
  }

  if ( record.inherited ){
    tooltipText = mainContainerData.getIn(['params', 'init', 'lang', 'btnRow_copyParams']);
    iconAlias = 'icon-connect';
  } else {
    tooltipText = mainContainerData.getIn(['params', 'init', 'lang', 'btnRow_deleteParams']);
    // this.draggable = false;
    iconAlias = 'icon-delete';
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

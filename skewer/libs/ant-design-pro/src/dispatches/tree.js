export const dispatchLoadTabs = (dispatch, itemId, module) => () => {
  dispatch({
    type: 'skGlobal/loadTabs',
    payload: {
      path: 'out.tabs',
      itemId,
      module,
    },
  });
};

export const dispatchGetSubItems = (dispatch, node, path) => {
  // const {path} = this.props;

  dispatch({
    type: 'skGlobal/getSubItems',
    payload: {
      path, // 'out.left.section',
      cmd: 'getSubItems',
      node,
    },
  });
};

export const dispatchGetForm = (dispatch, item, path) => {
  // const {path} = this.props;

  dispatch({
    type: 'skGlobal/getForm',
    payload: {
      path, // 'out.left.section',
      item,
    },
  });
};

export const dispatchToggleModalForm = (dispatch, payload) => {
  dispatch({
    type: 'skGlobal/toggleModalFormEditingSection',
    payload
  });
};

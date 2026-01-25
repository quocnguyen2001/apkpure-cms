class AppVersionAdminManagement {
    appVersionTableId = null

    initTable(tableId) {
        if (!tableId || !tableId.includes('app-version-table')) {
            return
        }

        this.appVersionTableId = tableId
    }

    reloadTable() {
        if (!this.appVersionTableId) {
            return
        }

        $(`#${this.appVersionTableId}`).DataTable().draw()
    }
}

$(() => {
    const appVersionAdmin = new AppVersionAdminManagement()

    document.addEventListener('core-table-init-completed', function (event) {
        appVersionAdmin.initTable(event.detail.table.prop('id'))
    })

    $(document)
        .on('show.bs.modal', '#app-version-modal', (e) => {
            const modal = $(e.currentTarget)
            const href = $(e.relatedTarget).prop('href')

            $httpClient
                .make()
                .withLoading(modal.find('.modal-content'))
                .get(href)
                .then(({ data }) => {
                    modal.find('.modal-header .modal-title').text(data.data.title)
                    modal.find('.modal-body').html(data.data.content)

                    Botble.initMediaIntegrate()
                    Botble.initResources()
                })
        })
        .on('click', '#app-version-modal button[type="submit"]', (e) => {
            e.preventDefault()

            const button = $(e.currentTarget)
            const modal = button.closest('.modal')
            const form = modal.find('form')

            $httpClient
                .make()
                .withLoading(form)
                .withButtonLoading(button)
                .post(form.prop('action'), form.serialize())
                .then(({ data }) => {
                    Botble.showSuccess(data.message)

                    modal.modal('hide')

                    appVersionAdmin.reloadTable()
                })
        })
})

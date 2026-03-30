    public function code() {
        return $this->belongsTo(Code::class, 'code_id', 'id');
    }